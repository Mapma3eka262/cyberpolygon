<?php

use App\Http\Controllers\Api\FlagController;
use App\Http\Controllers\Api\TimerController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // API для флагов
    Route::prefix('flags')->group(function () {
        Route::post('/submit', [FlagController::class, 'submit']);
        Route::get('/attempts', [FlagController::class, 'attempts']);
        Route::get('/stats', [FlagController::class, 'stats']);
    });
    
    // API для таймеров
    Route::prefix('timers')->group(function () {
        Route::get('/remaining/{teamTask}', [TimerController::class, 'remaining']);
        Route::get('/active', [TimerController::class, 'active']);
    });
    
    // API для команд
    Route::prefix('teams')->group(function () {
        Route::get('/{team}/stats', [TeamController::class, 'stats']);
        Route::get('/leaderboard', [TeamController::class, 'leaderboard']);
    });
});

// Публичные API
Route::get('/competition/status', function () {
    return response()->json([
        'start_date' => config('ctf.competition.start_date'),
        'end_date' => config('ctf.competition.end_date'),
        'registration_open' => config('ctf.competition.registration_open'),
    ]);
});