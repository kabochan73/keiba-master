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

        return view('races.show', compact('race'));
    }
}
