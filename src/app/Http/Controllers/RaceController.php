<?php

namespace App\Http\Controllers;

use App\Models\Race;

class RaceController extends Controller
{
    public function index()
    {
        $races = Race::orderByDesc('race_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('races.index', compact('races'));
    }

    public function show(Race $race)
    {
        $race->load(['lapTimes', 'entries.horse', 'entries.jockey', 'entries.trainer']);

        return view('races.show', compact('race'));
    }
}
