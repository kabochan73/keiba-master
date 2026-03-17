<?php

namespace App\Http\Controllers;

use App\Models\Jockey;

class JockeyController extends Controller
{
    public function show(Jockey $jockey)
    {
        $entries = $jockey->entries()
            ->with(['race.entries', 'horse'])
            ->whereHas('race')
            ->get()
            ->sortByDesc(fn($e) => $e->race->race_date);

        // レース内上がり順位マップ（entry_id => 1/2/3）
        $last3fRankMap = [];
        $processedRaces = [];
        foreach ($entries as $entry) {
            $raceId = $entry->race_id;
            if (in_array($raceId, $processedRaces)) continue;
            $processedRaces[] = $raceId;
            $entry->race->entries
                ->whereNotNull('last_3f')->where('last_3f', '>', 0)
                ->sortBy('last_3f')->values()->take(3)
                ->each(fn($e, $i) => $last3fRankMap[$e->id] = $i + 1);
        }

        $finished = $entries->whereNotNull('finish_position');

        // 全体成績
        $overall = $this->calcStats($finished);

        // グレード別
        $statsByGrade = $finished
            ->groupBy(fn($e) => $e->race->grade ?? '-')
            ->map(fn($g) => $this->calcStats($g));

        // 競馬場別
        $statsByVenue = $finished
            ->groupBy(fn($e) => $e->race->venue)
            ->map(fn($g) => $this->calcStats($g))
            ->sortKeys();

        // 距離別（200m単位でまとめる）
        $statsByDistance = $finished
            ->groupBy(fn($e) => $this->distanceGroup($e->race->distance))
            ->map(fn($g) => $this->calcStats($g))
            ->sortKeys();

        // 年別
        $statsByYear = $finished
            ->groupBy(fn($e) => $e->race->race_date?->format('Y'))
            ->map(fn($g) => $this->calcStats($g))
            ->sortKeysDesc();

        // 脚質別（コーナー位置から判定）
        $statsByStyle = $finished
            ->groupBy(fn($e) => $this->runningStyle($e))
            ->map(fn($g) => $this->calcStats($g));

        // 脚質の表示順
        $styleOrder = ['逃げ', '先行', '差し', '追込', '不明'];
        $statsByStyle = collect($styleOrder)
            ->filter(fn($s) => $statsByStyle->has($s))
            ->mapWithKeys(fn($s) => [$s => $statsByStyle[$s]]);

        return view('jockeys.show', compact(
            'jockey', 'entries', 'overall',
            'statsByGrade', 'statsByVenue', 'statsByDistance',
            'statsByYear', 'statsByStyle', 'last3fRankMap'
        ));
    }

    private function calcStats($entries): array
    {
        $total  = $entries->count();
        $first  = $entries->where('finish_position', 1)->count();
        $second = $entries->where('finish_position', 2)->count();
        $third  = $entries->where('finish_position', 3)->count();

        return [
            'total'       => $total,
            'first'       => $first,
            'second'      => $second,
            'third'       => $third,
            'other'       => $total - $first - $second - $third,
            'win_rate'    => $total > 0 ? round($first / $total * 100, 1) : 0,
            'place_rate'  => $total > 0 ? round(($first + $second + $third) / $total * 100, 1) : 0,
        ];
    }

    private function distanceGroup(?int $distance): string
    {
        if (!$distance) return '不明';
        $base = (int) floor($distance / 200) * 200;
        return "{$base}〜" . ($base + 199) . "m";
    }

    private function runningStyle($entry): string
    {
        // コーナー4の位置を使って脚質を判定
        $pos   = $entry->corner_4 ?? $entry->corner_3 ?? $entry->corner_2 ?? $entry->corner_1;
        $total = $entry->race->field_size;

        if (!$pos || !$total || $total <= 0) return '不明';

        $rate = $pos / $total;

        return match (true) {
            $rate <= 0.15 => '逃げ',
            $rate <= 0.35 => '先行',
            $rate <= 0.60 => '差し',
            default       => '追込',
        };
    }
}
