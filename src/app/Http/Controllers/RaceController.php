<?php

namespace App\Http\Controllers;

use App\Models\Race;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Race::query();

        // 年フィルタ
        if ($year = $request->input('year')) {
            $query->whereYear('race_date', $year);
        }

        // グレードフィルタ
        if ($grade = $request->input('grade')) {
            $query->where('grade', $grade);
        }

        // 開催場フィルタ
        if ($venue = $request->input('venue')) {
            $query->where('venue', $venue);
        }

        // レース名キーワード
        if ($keyword = $request->input('keyword')) {
            $query->where('race_name', 'like', "%{$keyword}%");
        }

        $races = $query->orderByDesc('race_date')->orderByDesc('id')->paginate(20)->withQueryString();

        // フィルタ用選択肢
        $years  = Race::selectRaw('YEAR(race_date) as year')->distinct()->orderByDesc('year')->pluck('year');
        $venues = Race::distinct()->orderBy('venue')->pluck('venue');

        return view('races.index', compact('races', 'years', 'venues'));
    }

    public function show(Race $race)
    {
        $race->load(['lapTimes', 'entries.horse', 'entries.jockey', 'entries.trainer']);

        // 同名レースの過去版（新しい順、自分自身を除く）
        $pastEditions = Race::where('race_name', $race->race_name)
            ->where('id', '!=', $race->id)
            ->with(['lapTimes', 'entries.horse'])
            ->orderByDesc('race_date')
            ->get();

        // 各レースの上がり平均を計算するヘルパー
        $calcAvgLast3f = fn($r) => $r->entries
            ->whereNotNull('last_3f')
            ->where('last_3f', '>', 0)
            ->avg('last_3f');

        // 過去の勝ちタイム平均（タイムインデックス算出用）
        $pastWinTimes = $pastEditions->map(
            fn($r) => $r->entries->where('finish_position', 1)->first()?->finish_time
        )->filter();

        $avgWinTime    = $pastWinTimes->count() > 0 ? $pastWinTimes->avg() : null;
        $currentWinner = $race->entries->where('finish_position', 1)->first();
        $timeIndex     = ($avgWinTime && $currentWinner?->finish_time)
            ? round($avgWinTime - $currentWinner->finish_time, 2)
            : null;

        return view('races.show', compact('race', 'pastEditions', 'calcAvgLast3f', 'avgWinTime', 'timeIndex'));
    }

    public function updateComment(Request $request, Race $race)
    {
        $race->update(['comment' => $request->input('comment')]);

        return back()->with('comment_saved', true);
    }
}
