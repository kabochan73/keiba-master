<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 競馬分析API
Route::prefix('v1')->group(function () {
    Route::get('/races', function () {
        return response()->json(['message' => 'レース一覧API（実装予定）']);
    });

    Route::get('/horses', function () {
        return response()->json(['message' => '馬一覧API（実装予定）']);
    });
});
