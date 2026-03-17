<?php

namespace App\Http\Controllers;

use App\Models\Horse;

class HorseController extends Controller
{
    public function show(Horse $horse)
    {
        $horse->load(['trainer', 'breeder']);

        // 過去成績（新しい順）
        $entries = $horse->entries()
            ->with(['race', 'jockey', 'correction'])
            ->whereHas('race')
            ->get()
            ->sortByDesc(fn($e) => $e->race->race_date);

        // 距離別成績
        $statsByDistance = $entries
            ->whereNotNull('finish_position')
            ->groupBy(fn($e) => $e->race->distance)
            ->map(fn($g) => $this->calcStats($g))
            ->sortKeys();

        // グレード別成績
        $statsByGrade = $entries
            ->whereNotNull('finish_position')
            ->groupBy(fn($e) => $e->race->grade ?? '-')
            ->map(fn($g) => $this->calcStats($g));

        return view('horses.show', compact('horse', 'entries', 'statsByDistance', 'statsByGrade'));
    }

    private function calcStats($entries): array
    {
        $total  = $entries->count();
        $first  = $entries->where('finish_position', 1)->count();
        $second = $entries->where('finish_position', 2)->count();
        $third  = $entries->where('finish_position', 3)->count();

        return [
            'total'      => $total,
            'first'      => $first,
            'second'     => $second,
            'third'      => $third,
            'win_rate'   => $total > 0 ? round($first / $total * 100) : 0,
            'place_rate' => $total > 0 ? round(($first + $second + $third) / $total * 100) : 0,
        ];
    }
}
