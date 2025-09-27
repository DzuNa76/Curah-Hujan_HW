<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RainfallDataController;

// Beranda -> redirect ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Test route
Route::get('/test', function () {
    return view('test');
});


// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login-simple', function () {
    return view('auth.login-simple');
});
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

// Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');})->name('password.request');

// Dashboard (hanya untuk user login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Data Peramalan
Route::resource('rainfall', RainfallDataController::class);