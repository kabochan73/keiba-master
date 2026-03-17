<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaceCorrectionController;

Route::get('/', fn() => redirect()->route('races.index'));
Route::resource('races', RaceController::class)->only(['index', 'show']);
Route::get('races/{race}/corrections', [RaceCorrectionController::class, 'edit'])->name('races.corrections.edit');
Route::post('races/{race}/corrections', [RaceCorrectionController::class, 'update'])->name('races.corrections.update');
