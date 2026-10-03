<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/start/{$i}', [DashboardController::class, 'start'])->name('start');
    Route::get('/dashboard/stop/{$i}', [DashboardController::class, 'stop'])->name('stop');
    
    Route::get('/graph', [GraphController::class, 'index'])->name('graph');

    Route::get('/setting', [SettingController::class, 'index'])->name('setting');
    
});

require __DIR__.'/auth.php';
