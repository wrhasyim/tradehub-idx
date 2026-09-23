<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ChartDataController;

Route::get('/', function () {
    return view('welcome');
});

// Rute Khusus Admin (Command Center)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::patch('/user/{user}/vip', [AdminController::class, 'toggleVip'])->name('user.vip');
});

// Rute Dashboard menggunakan Controller
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rute Bawaan & Fitur Utama
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/upgrade/process', [SubscriptionController::class, 'process'])->name('upgrade.process');

    // Trading Journal Routes
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');
    Route::delete('/journal/{journal}', [JournalController::class, 'destroy'])->name('journal.destroy');
});

// Endpoint untuk mengambil data OHLCV Chart (Bisa diakses publik, logika VIP diatur di controller)
Route::get('/api/chart/{code}', [ChartDataController::class, 'getOhlcvData'])->name('api.chart.data');

Route::get('/terminal', function () {
    return view('terminal.index');
})->name('terminal.index');

require __DIR__.'/auth.php';