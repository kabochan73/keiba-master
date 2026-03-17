<?php

namespace App\Services\Scraping;

use Illuminate\Support\Facades\Log;

class RaceDetailScraper
{
    public function __construct(private NetkeibaClient $client) {}

    /**
     * レース詳細（結果・出走馬・ラップ）を取得して返す
     */
    public function scrape(string $raceId): ?array
    {
        $resultUrl = "https://race.netkeiba.com/race/result.html?race_id={$raceId}";
        $lapUrl    = "https://db.netkeiba.com/race/{$raceId}/";

        $resultCrawler = $this->client->fetch($resultUrl);
        if (!$resultCrawler) {
            Log::error("レース結果取得失敗: {$raceId}");
            return null;
        }

        $lapCrawler = $this->client->fetch($lapUrl);

        return [
            'race'    => $this->parseRaceInfo($resultCrawler, $raceId),
            'entries' => $this->parseEntries($resultCrawler),
            'laps'    => $lapCrawler ? $this->parseLapTimes($lapCrawler) : [],
        ];
    }

    /**
     * レース基本情報をパース
     */
    private function parseRaceInfo($crawler, string $raceId): array
    {
        $info = [
            'race_id'     => $raceId,
            'race_name'   => '',
            'race_date'   => '',
            'venue'       => '',
            'race_number' => 0,
            'course_type' => '芝',
            'distance'    => 0,
            'weather'     => null,
            'track_condition' => null,
            'grade'       => null,
            'field_size'  => 0,
            'race_url'    => "https://race.netkeiba.com/race/result.html?race_id={$raceId}",
        ];

        try {
            // レース名
            $info['race_name'] = trim($crawler->filter('.RaceName')->text(''));

            // グレード
            if ($crawler->filter('.Icon_GradeType1')->count() > 0) {
                $info['grade'] = 'G1';
            } elseif ($crawler->filter('.Icon_GradeType2')->count() > 0) {
                $info['grade'] = 'G2';
            } elseif ($crawler->filter('.Icon_GradeType3')->count() > 0) {
                $info['grade'] = 'G3';
            }

            // レースデータ1行目（距離・天候・馬場）
            // 実際の出力例: "15:30発走 / 芝3000m (右 A) / 天候:晴 / 馬場:良"
            $raceData01 = $crawler->filter('.RaceData01')->text('');
            if (preg_match('/(\d+)m/', $raceData01, $m)) {
                $info['distance'] = (int) $m[1];
            }
            if (str_contains($raceData01, 'ダート')) {
                $info['course_type'] = 'ダート';
            }
            // 天候（"天候:晴" or "天候：晴"）
            if (preg_match('/天候[：:](\S+)/', $raceData01, $m)) {
                $info['weather'] = rtrim($m[1], '/');
            }
            // 馬場（"馬場:良" or "芝:良" or "ダ:良"）
            if (preg_match('/馬場[：:](\S+)/', $raceData01, $m)
                || preg_match('/芝[：:](\S+)/', $raceData01, $m)
                || preg_match('/ダ[：:](\S+)/', $raceData01, $m)) {
                $info['track_condition'] = rtrim($m[1], '/');
            }
            // 回り方向（右/左）
            if (str_contains($raceData01, '右')) {
                $info['turn_direction'] = '右';
            } elseif (str_contains($raceData01, '左')) {
                $info['turn_direction'] = '左';
            }

            // 日付: .RaceData02内のリンク（kaisai_dateパラメータ）から取得
            // 例: <a href="/top/race_list.html?kaisai_date=20250323">2025年3月23日</a>
            $dateFound = false;
            $crawler->filter('.RaceData02 a')->each(function ($a) use (&$info, &$dateFound) {
                if ($dateFound) return;
                $href = $a->attr('href') ?? '';
                if (preg_match('/kaisai_date=(\d{4})(\d{2})(\d{2})/', $href, $m)) {
                    $info['race_date'] = "{$m[1]}-{$m[2]}-{$m[3]}";
                    $dateFound = true;
                }
                // テキストから日付パターン
                if (!$dateFound && preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/', $a->text(''), $m)) {
                    $info['race_date'] = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                    $dateFound = true;
                }
            });
            // フォールバック：ページ内のkaisai_dateリンクを広く探す
            if (!$dateFound) {
                $crawler->filter('a[href*="kaisai_date"]')->each(function ($a) use (&$info, &$dateFound) {
                    if ($dateFound) return;
                    $href = $a->attr('href') ?? '';
                    if (preg_match('/kaisai_date=(\d{4})(\d{2})(\d{2})/', $href, $m)) {
                        $info['race_date'] = "{$m[1]}-{$m[2]}-{$m[3]}";
                        $dateFound = true;
                    }
                });
            }
            if (!$dateFound) {
                Log::warning("race_date取得失敗（race_id: {$raceId}）");
                $info['race_date'] = substr($raceId, 0, 4) . '-01-01';
            }

            // レース番号
            $raceData02Text = $crawler->filter('.RaceData02')->text('');
            if (preg_match('/(\d+)R/', $raceData02Text, $m)) {
                $info['race_number'] = (int) $m[1];
            }

            // 開催場所（race_idの競馬場コードから）
            $info['venue'] = $this->getVenueFromRaceId($raceId);

            // 出走頭数
            $info['field_size'] = $crawler->filter('#All_Result_Table tbody tr')->count();

        } catch (\Exception $e) {
            Log::error("レース情報パース失敗: {$raceId}", ['error' => $e->getMessage()]);
        }

        return $info;
    }

    /**
     * 出走馬の結果をパース
     */
    private function parseEntries($crawler): array
    {
        $entries = [];

        try {
            $crawler->filter('#All_Result_Table tbody tr')->each(function ($row) use (&$entries) {
                $cells = $row->filter('td');
                if ($cells->count() < 10) {
                    return;
                }

                $entry = [
                    'finish_position' => null,
                    'post_position'   => null,
                    'horse_number'    => null,
                    'horse_id'        => null,
                    'horse_name'      => '',
                    'age'             => null,
                    'sex'             => null,
                    'burden_weight'   => null,
                    'jockey_id'       => null,
                    'jockey_name'     => '',
                    'finish_time'     => null,
                    'time_diff'       => null,
                    'corner_1'        => null,
                    'corner_2'        => null,
                    'corner_3'        => null,
                    'corner_4'        => null,
                    'last_3f'         => null,
                    'odds'            => null,
                    'popularity'      => null,
                    'weight'          => null,
                    'weight_change'   => null,
                    'trainer_id'      => null,
                    'trainer_name'    => '',
                ];

                try {
                    // 着順
                    $posText = trim($cells->eq(0)->text(''));
                    $entry['finish_position'] = is_numeric($posText) ? (int) $posText : null;

                    // 枠番
                    $entry['post_position'] = (int) trim($cells->eq(1)->text(''));

                    // 馬番
                    $entry['horse_number'] = (int) trim($cells->eq(2)->text(''));

                    // 馬名・馬ID
                    $horseLink = $cells->eq(3)->filter('a')->first();
                    if ($horseLink->count() > 0) {
                        $entry['horse_name'] = $this->clean($horseLink->text(''));
                        preg_match('/horse\/(\w+)/', $horseLink->attr('href') ?? '', $m);
                        $entry['horse_id'] = $m[1] ?? null;
                    }

                    // 性齢（例: 牡4）
                    $sexAge = $this->clean($cells->eq(4)->text(''));
                    if (preg_match('/([牡牝騸セ])(\d+)/u', $sexAge, $m)) {
                        $entry['sex'] = $m[1];
                        $entry['age'] = (int) $m[2];
                    }

                    // 斤量
                    $entry['burden_weight'] = (float) trim($cells->eq(5)->text(''));

                    // 騎手名・騎手ID
                    $jockeyLink = $cells->eq(6)->filter('a')->first();
                    if ($jockeyLink->count() > 0) {
                        $entry['jockey_name'] = $this->clean($jockeyLink->text(''));
                        preg_match('/jockey\/result\/recent\/(\w+)/', $jockeyLink->attr('href') ?? '', $m);
                        $entry['jockey_id'] = $m[1] ?? null;
                    }

                    // タイム（例: 1:58.2 → 秒換算）
                    $timeText = trim($cells->eq(7)->text(''));
                    $entry['finish_time'] = $this->parseTimeToSeconds($timeText);

                    // 着差
                    $entry['time_diff'] = trim($cells->eq(8)->text(''));

                    // 人気
                    $entry['popularity'] = (int) trim($cells->eq(9)->text(''));

                    // オッズ
                    $entry['odds'] = (float) trim($cells->eq(10)->text(''));

                    // 上がり3F
                    $entry['last_3f'] = (float) trim($cells->eq(11)->text(''));

                    // コーナー通過順（例: "2-2-3-4"）
                    $cornerText = trim($cells->eq(12)->text(''));
                    $corners = explode('-', $cornerText);
                    $entry['corner_1'] = isset($corners[0]) && is_numeric($corners[0]) ? (int) $corners[0] : null;
                    $entry['corner_2'] = isset($corners[1]) && is_numeric($corners[1]) ? (int) $corners[1] : null;
                    $entry['corner_3'] = isset($corners[2]) && is_numeric($corners[2]) ? (int) $corners[2] : null;
                    $entry['corner_4'] = isset($corners[3]) && is_numeric($corners[3]) ? (int) $corners[3] : null;

                    // 調教師名・調教師ID
                    $trainerLink = $cells->eq(13)->filter('a')->first();
                    if ($trainerLink->count() > 0) {
                        $entry['trainer_name'] = $this->clean($trainerLink->text(''));
                        preg_match('/trainer\/result\/recent\/(\w+)/', $trainerLink->attr('href') ?? '', $m);
                        $entry['trainer_id'] = $m[1] ?? null;
                    }

                    // 馬体重（例: "480(+2)"）
                    $weightText = trim($cells->eq(14)->text(''));
                    if (preg_match('/(\d+)\(([+-]?\d+)\)/', $weightText, $m)) {
                        $entry['weight']        = (int) $m[1];
                        $entry['weight_change'] = (int) $m[2];
                    }

                } catch (\Exception $e) {
                    Log::warning("出走馬データパース失敗", ['error' => $e->getMessage()]);
                }

                if ($entry['horse_id']) {
                    $entries[] = $entry;
                }
            });
        } catch (\Exception $e) {
            Log::error("出走馬一覧パース失敗", ['error' => $e->getMessage()]);
        }

        return $entries;
    }

    /**
     * ラップタイムをパース（db.netkeiba.com/race/RACE_ID/ から）
     * 形式: "13.2 - 11.9 - 12.6 - ..."（1セルに全ラップ）
     */
    private function parseLapTimes($crawler): array
    {
        $laps = [];

        try {
            // テーブルを全て走査し、"ラップタイム"を含む行を探す
            $lapText = '';

            $crawler->filter('table')->each(function ($table) use (&$lapText) {
                if (!empty($lapText)) return;
                $text = $table->text('');
                if (str_contains($text, 'ラップタイム') || preg_match('/\d+\.\d+ - \d+\.\d+/', $text)) {
                    // 最初の行（個別ラップ）を取得
                    $firstRow = $table->filter('tr')->first()->filter('td')->first();
                    if ($firstRow->count() > 0) {
                        $lapText = trim($firstRow->text(''));
                    }
                }
            });

            if (empty($lapText)) {
                return [];
            }

            // "13.2 - 11.9 - 12.6" 形式をパース
            $parts = preg_split('/\s*-\s*/', $lapText);
            $lapNumber = 1;
            foreach ($parts as $part) {
                $part = trim($part);
                if (is_numeric($part) && (float) $part > 0) {
                    $laps[] = [
                        'lap_number' => $lapNumber++,
                        'lap_time'   => (float) $part,
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error("ラップタイムパース失敗", ['error' => $e->getMessage()]);
        }

        return $laps;
    }

    /**
     * タイム文字列を秒に変換（例: "1:58.2" → 118.2）
     */
    private function parseTimeToSeconds(string $timeText): ?float
    {
        $timeText = trim($timeText);
        if (empty($timeText) || $timeText === '---') {
            return null;
        }

        if (preg_match('/(\d+):(\d+)\.(\d+)/', $timeText, $m)) {
            return (int) $m[1] * 60 + (int) $m[2] + (float) ('0.' . $m[3]);
        }

        if (preg_match('/(\d+)\.(\d+)/', $timeText, $m)) {
            return (float) $timeText;
        }

        return null;
    }

    /**
     * race_idの競馬場コードから開催場所名を取得
     */
    private function getVenueFromRaceId(string $raceId): string
    {
        $venueCode = substr($raceId, 4, 2);

        return match ($venueCode) {
            '01' => '札幌',
            '02' => '函館',
            '03' => '福島',
            '04' => '新潟',
            '05' => '東京',
            '06' => '中山',
            '07' => '中京',
            '08' => '京都',
            '09' => '阪神',
            '10' => '小倉',
            default => '不明',
        };
    }

    /**
     * DomCrawlerノードのテキストを安全なUTF-8で返す
     */
    private function clean(string $text): string
    {
        return NetkeibaClient::cleanText($text);
    }
}
