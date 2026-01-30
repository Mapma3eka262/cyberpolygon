<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ArenaController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// Политика конфиденциальности
Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

// Аутентификация
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginRegister'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Выход
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Защищенные маршруты
Route::middleware(['auth', 'check.active.team'])->group(function () {
    // Личный кабинет
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::post('/dashboard/team/add-member', [DashboardController::class, 'addTeamMember'])
        ->name('dashboard.team.add-member')
        ->middleware('check.captain');
    
    // Арена
    Route::get('/arena', [ArenaController::class, 'index'])->name('arena');
    Route::post('/arena/submit', [ArenaController::class, 'submitFlag'])->name('arena.submit');
    
    // Профиль
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Админка
require __DIR__.'/admin.php';

// API
require __DIR__.'/api.php';