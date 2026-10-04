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
    Route::get('/dashboard/start/{button_id}', [DashboardController::class, 'start'])->name('start');
    Route::get('/dashboard/stop/{button_id}', [DashboardController::class, 'stop'])->name('stop');
    
    Route::get('/graph', [GraphController::class, 'index'])->name('graph');

    Route::get('/setting', [SettingController::class, 'index'])->name('setting');
    Route::get('/setting/download/{id}', [SettingController::class, 'download'])->name('download');
    Route::get('/setting/create', [SettingController::class, 'create'])->name('create');
    Route::post('/setting/store', [SettingController::class, 'store'])->name('store');
    Route::get('/setting/update/{id}', [SettingController::class, 'update'])->name('update');
    Route::post('/setting/patch', [SettingController::class, 'patch'])->name('patch');
    Route::get('/setting/delete/{id}', [SettingController::class, 'delete'])->name('delete');
    Route::post('/setting/remove', [SettingController::class, 'remove'])->name('remove');
    
});

require __DIR__.'/auth.php';
