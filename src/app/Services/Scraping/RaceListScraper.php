<?php

namespace App\Services\Scraping;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RaceListScraper
{
    public function __construct(private NetkeibaClient $client) {}

    /**
     * 指定年の対象レース（芝・牝馬混合・重賞）のrace_idリストを返す
     */
    public function getRaceIdsByYear(int $year): array
    {
        $raceDates = $this->getRaceDates($year);
        $raceIds = [];

        foreach ($raceDates as $date) {
            $ids = $this->getRaceIdsByDate($date);
            $raceIds = array_merge($raceIds, $ids);
            Log::info("日付 {$date}: " . count($ids) . " 件の対象レース取得");
        }

        return array_unique($raceIds);
    }

    /**
     * 指定日の対象レースのrace_idリストを返す
     */
    public function getRaceIdsByDate(string $date): array
    {
        $url = "https://race.netkeiba.com/top/race_list.html?kaisai_date={$date}";
        $crawler = $this->client->fetch($url);

        if (!$crawler) {
            return [];
        }

        $raceIds = [];

        try {
            // 各レースブロックを探索
            $crawler->filter('.RaceList_DataItem')->each(function ($node) use (&$raceIds) {
                // 重賞フィルタ（G1/G2/G3アイコンがあるか）
                $isGrade = $node->filter('.Icon_GradeType1, .Icon_GradeType2, .Icon_GradeType3')->count() > 0;
                if (!$isGrade) {
                    return;
                }

                // コース情報テキスト（「芝」を含むか）
                $courseText = '';
                try {
                    $courseText = $node->filter('.RaceData')->text('');
                } catch (\Exception $e) {}

                $isTurf = str_contains($courseText, '芝');
                if (!$isTurf) {
                    return;
                }

                // レース条件（牝馬混合か）
                // 牝馬限定は除外、牝馬混合（混合戦）を対象とする
                $conditionText = '';
                try {
                    $conditionText = $node->filter('.RaceData02')->text('');
                } catch (\Exception $e) {}

                // 「牡・牝・騸」や「混合」「オープン」等が含まれる = 牝馬混合
                // 「牝」のみ = 牝馬限定（除外）
                // netkeibaの条件表記に基づいてフィルタ
                $isMixed = $this->isMixedRace($conditionText);
                if (!$isMixed) {
                    return;
                }

                // race_idをリンクから取得
                try {
                    $link = $node->filter('a[href*="race_id"]')->first();
                    if ($link->count() > 0) {
                        $href = $link->attr('href');
                        preg_match('/race_id=(\d+)/', $href, $matches);
                        if (!empty($matches[1])) {
                            $raceIds[] = $matches[1];
                        }
                    }
                } catch (\Exception $e) {}
            });
        } catch (\Exception $e) {
            Log::error("レース一覧パース失敗: {$date}", ['error' => $e->getMessage()]);
        }

        return $raceIds;
    }

    /**
     * 指定年の開催日（土日）リストを返す
     */
    private function getRaceDates(int $year): array
    {
        $dates = [];
        $start = Carbon::create($year, 1, 1);
        $end   = Carbon::create($year, 12, 31);

        $current = $start->copy();
        while ($current->lte($end)) {
            // 土曜・日曜のみ
            if ($current->isSaturday() || $current->isSunday()) {
                $dates[] = $current->format('Ymd');
            }
            $current->addDay();
        }

        return $dates;
    }

    /**
     * 牝馬混合レースかどうかを判定
     * 「牝」のみの限定戦は除外、混合戦は対象
     */
    private function isMixedRace(string $conditionText): bool
    {
        // 牝馬限定の表記（「牝」のみで「牡」や「騸」を含まない）
        if (preg_match('/牝\s*\(/', $conditionText) && !str_contains($conditionText, '牡')) {
            return false;
        }

        // 混合（牡・牝・騸 が混在）を対象
        // 条件テキストに何も限定がない、または混合表記がある
        return true;
    }
}
