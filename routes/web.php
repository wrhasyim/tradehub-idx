<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController; // <- Baris ini yang paling penting
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\JournalController;

Route::get('/', function () {
    return view('welcome');
});

// Rute Khusus Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/store', [AdminController::class, 'store'])->name('admin.store');
    Route::delete('/destroy/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
});

// Rute Dashboard menggunakan Controller yang baru kita buat
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rute Bawaan Laravel Breeze
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

require __DIR__.'/auth.php';