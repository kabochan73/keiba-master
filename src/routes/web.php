<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HorseController;
use App\Http\Controllers\JockeyController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\RaceCorrectionController;

Route::get('/', fn() => redirect()->route('races.index'));
Route::resource('races', RaceController::class)->only(['index', 'show']);
Route::patch('races/{race}/comment', [RaceController::class, 'updateComment'])->name('races.comment.update');
Route::get('horses/{horse}', [HorseController::class, 'show'])->name('horses.show');
Route::get('jockeys/{jockey}', [JockeyController::class, 'show'])->name('jockeys.show');
Route::get('races/{race}/corrections', [RaceCorrectionController::class, 'edit'])->name('races.corrections.edit');
Route::post('races/{race}/corrections', [RaceCorrectionController::class, 'update'])->name('races.corrections.update');
