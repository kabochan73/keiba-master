<?php

namespace App\Services\Scraping;

use Illuminate\Support\Facades\Log;

class RaceListScraper
{
    // JRA全場コード
    private const VENUES = ['01','02','03','04','05','06','07','08','09','10'];

    public function __construct(private NetkeibaClient $client) {}

    /**
     * 指定年の対象レース（芝・牝馬混合・重賞）のrace_idリストを返す
     */
    public function getRaceIdsByYear(int $year): array
    {
        $raceIds = [];

        // 月ごとに検索（1ページあたり50件、ページネーション対応）
        for ($month = 1; $month <= 12; $month++) {
            // 現在日より未来の月はスキップ
            $now = now();
            if ($year > $now->year || ($year === $now->year && $month > $now->month)) {
                continue;
            }

            $ids = $this->getRaceIdsByMonth($year, $month);
            $raceIds = array_merge($raceIds, $ids);
            Log::info("{$year}年{$month}月: " . count($ids) . " 件の対象レース取得");
        }

        return array_unique($raceIds);
    }

    /**
     * 指定日の対象レースのrace_idリストを返す（単日スクレイプ用）
     */
    public function getRaceIdsByDate(string $date): array
    {
        $year  = (int) substr($date, 0, 4);
        $month = (int) substr($date, 4, 2);

        return $this->getRaceIdsByMonth($year, $month, $date);
    }

    /**
     * db.netkeiba.com の検索ページから芝・重賞レースのrace_idを取得
     */
    private function getRaceIdsByMonth(int $year, int $month, ?string $filterDate = null): array
    {
        $raceIds = [];
        $page    = 1;

        do {
            $url = $this->buildSearchUrl($year, $month, $page);
            $crawler = $this->client->fetch($url);

            if (!$crawler) {
                break;
            }

            $found = 0;

            try {
                // 結果テーブルの各行を走査
                $crawler->filter('table.race_table_01 tr, table.nk_tb_common tr')->each(
                    function ($row) use (&$raceIds, &$found, $filterDate) {
                        // レースへのリンクを探す（/race/RACEID/ 形式）
                        // a要素を取得してPHPでhrefをチェック
                        $href = null;
                        try {
                            $row->filter('a')->each(function ($a) use (&$href) {
                                if ($href !== null) {
                                    return;
                                }
                                $h = $a->attr('href') ?? '';
                                if (preg_match('#/race/\d{12}/?#', $h)) {
                                    $href = $h;
                                }
                            });
                        } catch (\Exception) {}

                        if ($href === null) {
                            return;
                        }

                        if (!preg_match('#/race/(\d{12})/?#', $href, $m)) {
                            return;
                        }

                        $raceId = $m[1];
                        $found++;

                        // 行のテキストを取得（日付・芝チェックに使用）
                        $rowText = '';
                        try {
                            $rowText = $row->text('');
                        } catch (\Exception) {}

                        // 日付フィルタ（行テキストの先頭 YYYY/MM/DD を使用）
                        if ($filterDate) {
                            // 行テキストから "2025/03/16" 形式の日付を抽出
                            if (!preg_match('/(\d{4})\/(\d{2})\/(\d{2})/', $rowText, $dm)) {
                                return;
                            }
                            $rowDateStr = $dm[1] . $dm[2] . $dm[3]; // "20250316"
                            if ($rowDateStr !== $filterDate) {
                                return;
                            }
                        }

                        // 芝チェック（db.netkeiba.comの表記: "芝1200" 等）
                        if (!str_contains($rowText, '芝')) {
                            return;
                        }

                        // 牝馬限定レースは除外
                        if ($this->isFemaleOnly($rowText)) {
                            return;
                        }

                        $raceIds[] = $raceId;
                    }
                );
            } catch (\Exception $e) {
                Log::error("レース検索パース失敗: {$year}/{$month} page={$page}", [
                    'error' => $e->getMessage(),
                ]);
                break;
            }

            // 次のページがなければ終了
            $hasNext = false;
            try {
                $hasNext = $crawler->filter('a.pager_next, .pager a[href*="page=' . ($page + 1) . '"]')->count() > 0;
            } catch (\Exception $e) {}

            $page++;

            // 安全のため最大20ページ
            if ($page > 20) {
                break;
            }

            // ページが存在しても結果0件なら終了
            if ($found === 0) {
                break;
            }

        } while ($hasNext);

        return $raceIds;
    }

    /**
     * db.netkeiba.com の芝重賞検索URL を生成
     */
    private function buildSearchUrl(int $year, int $month, int $page = 1): string
    {
        $params = [
            'pid'        => 'race_list',
            'word'       => '',
            'start_year' => $year,
            'start_mon'  => $month,
            'end_year'   => $year,
            'end_mon'    => $month,
            'kyori_min'  => '',
            'kyori_max'  => '',
            'list'       => 50,
            'page'       => $page,
            'submit'     => '検索',
        ];

        // JRA全場
        foreach (self::VENUES as $v) {
            $params["jyo[{$v}]"] = $v;
        }

        // 重賞のみ (G1=1, G2=2, G3=3)
        $params['grade[1]'] = 1;
        $params['grade[2]'] = 2;
        $params['grade[3]'] = 3;

        // 芝のみ
        $params['type[T]'] = 'T';

        // 配列パラメータを手動ビルド（http_build_query は [] エンコードが異なる）
        $base = 'https://db.netkeiba.com/?';
        $parts = [];

        foreach ($params as $key => $value) {
            // jyo[01]=01 → jyo[]=01 形式に変換
            if (preg_match('/^(jyo|grade|type)\[/', $key)) {
                $arrayKey = preg_replace('/\[.*\]$/', '[]', $key);
                $parts[] = urlencode($arrayKey) . '=' . urlencode((string) $value);
            } else {
                $parts[] = urlencode($key) . '=' . urlencode((string) $value);
            }
        }

        return $base . implode('&', $parts);
    }

    /**
     * 牝馬限定レースかどうかを判定
     */
    private function isFemaleOnly(string $text): bool
    {
        // 「牝」を含み「牡」「騸」を含まない = 牝馬限定
        if (mb_strpos($text, '牝') !== false
            && mb_strpos($text, '牡') === false
            && mb_strpos($text, '騸') === false) {
            return true;
        }

        return false;
    }
}
