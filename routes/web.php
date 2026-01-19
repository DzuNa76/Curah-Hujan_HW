<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController,
    RainfallDataController,
    UserController,
    // ForecastingController,
    ForecastingMultiplicativeController,
    RegencyController,
    DistrictController,
    VillageController,
    StationController
};

// =====================================================================
// 🔐 Auth & Public Routes
// =====================================================================

// Redirect root ke login
Route::get('/', fn() => redirect()->route('login'));

// Login & Register
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password (static view)
Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');

// =====================================================================
// 🧭 Dashboard & Protected Routes
// =====================================================================
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 🌧️ Data Curah Hujan
    Route::prefix('rainfall')->name('rainfall.')->group(function () {
        Route::get('/', [RainfallDataController::class, 'index'])->name('index');
        Route::get('/create', [RainfallDataController::class, 'create'])->name('create');
        Route::post('/', [RainfallDataController::class, 'store'])->name('store');
        Route::get('/{station_id}/{id}/edit', [RainfallDataController::class, 'edit'])->name('edit');
        Route::put('/{station_id}/{id}', [RainfallDataController::class, 'update'])->name('update');
        Route::delete('/{station_id}/{id}', [RainfallDataController::class, 'destroy'])->name('destroy');
    });

    // 🖨️ Cetak Data Curah Hujan
    Route::post('/data/cetak', [RainfallDataController::class, 'cetak'])->name('data.cetak');
    Route::get('/data/available-months', [RainfallDataController::class, 'getAvailableMonths'])->name('data.available-months');


    // 📈 Forecasting
    Route::get('/forecasting', [ForecastingMultiplicativeController::class, 'index'])->name('forecasting.index');
    Route::post('/forecasting/process', [ForecastingMultiplicativeController::class, 'process'])->name('forecasting.process');
    Route::post('/forecasting/print', [ForecastingMultiplicativeController::class, 'print'])->name('forecasting.print');
    Route::get('/forecasting/available-dates', [ForecastingMultiplicativeController::class, 'getAvailableDates'])->name('forecasting.available-dates');

    // =================================================================
    // 🗺️ Master Data Wilayah (Kabupaten, Kecamatan, Desa, Stasiun)
    // =================================================================
    Route::prefix('master')->group(function () {
        Route::resource('regencies', RegencyController::class)->names('regencies');
        Route::resource('districts', DistrictController::class)->names('districts');
        Route::resource('villages', VillageController::class)->names('villages');
        Route::resource('stations', StationController::class)->names('stations');
        Route::get('stations/{station}/print', [StationController::class, 'print'])->name('stations.print');
    });

    // 👤 Edit Profil Pribadi (User bisa akses sendiri)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'editProfile'])->name('edit');
        Route::put('/', [UserController::class, 'updateProfile'])->name('update');
    });

    // 👤 User Management (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});