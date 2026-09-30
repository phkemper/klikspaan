<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('welcome');
});

// Gast-routes (alleen toegankelijk als je NIET bent ingelogd)
Route::middleware('guest')->group(function () {
    // Registratie
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    
    // Inloggen
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
    
// Beveiligde routes (alleen toegankelijk als je WEL bent ingelogd)
Route::middleware('auth')->group(function () {
    // Uitloggen
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    
    // Dashboard / Beveiligde pagina
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
