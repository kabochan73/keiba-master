<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceCorrection;
use Illuminate\Http\Request;

class RaceCorrectionController extends Controller
{
    public function edit(Race $race)
    {
        $race->load(['entries.horse', 'entries.jockey', 'entries.correction']);

        $entries = $race->entries->sortBy('finish_position');

        // 補正タイムで順位を計算（補正なしの馬は実タイムを使用）
        $correctedRanks = [];
        $rank = 1;
        foreach (
            $entries
                ->filter(fn($e) => $e->finish_time)
                ->sortBy(fn($e) => $e->correction?->corrected_time ?? $e->finish_time)
            as $entry
        ) {
            $correctedRanks[$entry->id] = $rank++;
        }

        // 補正データがあれば補正順位順、なければ着順で表示
        $hasCorrections = $entries->some(fn($e) => $e->correction?->corrected_time !== null);
        if ($hasCorrections) {
            $entries = $entries->sortBy(fn($e) => $correctedRanks[$e->id] ?? 999);
        }

        return view('races.corrections', compact('race', 'entries', 'correctedRanks'));
    }

    public function update(Request $request, Race $race)
    {
        $data = $request->input('corrections', []);

        foreach ($data as $entryId => $values) {
            $correction = RaceCorrection::firstOrNew(['race_entry_id' => $entryId]);

            $correction->distance_loss     = (float) ($values['distance_loss']     ?? 0);
            $correction->interference      = (float) ($values['interference']      ?? 0);
            $correction->slow_start        = (float) ($values['slow_start']        ?? 0);
            $correction->jockey_correction = (float) ($values['jockey_correction'] ?? 0);
            $correction->other_correction  = (float) ($values['other_correction']  ?? 0);
            $correction->note              = $values['note'] ?? null;
            $correction->save();

            $correction->recalculate();
        }

        return redirect()->route('races.corrections.edit', $race)
            ->with('success', '補正を保存しました');
    }
}
