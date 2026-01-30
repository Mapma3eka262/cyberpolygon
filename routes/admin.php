<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'check.admin'])->group(function () {
    // Главная админки
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Управление командами
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/create', [TeamController::class, 'create'])->name('create');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::put('/{team}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');
        Route::post('/{team}/toggle-status', [TeamController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{team}/members', [TeamController::class, 'members'])->name('members');
    });
    
    // Управление заданиями
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::get('/{task}/assign', [TaskController::class, 'assignForm'])->name('assign.form');
        Route::post('/{task}/assign', [TaskController::class, 'assign'])->name('assign');
        Route::post('/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Аналитика
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/system-metrics', [AnalyticsController::class, 'systemMetrics'])->name('system-metrics');
        Route::get('/user-activity', [AnalyticsController::class, 'userActivity'])->name('user-activity');
        Route::get('/database-stats', [AnalyticsController::class, 'databaseStats'])->name('database-stats');
        Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
    });
    
    // Статистика
    Route::prefix('stats')->name('stats.')->group(function () {
        Route::get('/', [StatsController::class, 'index'])->name('index');
        Route::get('/live', [StatsController::class, 'live'])->name('live');
        Route::get('/export', [StatsController::class, 'export'])->name('export');
    });
});