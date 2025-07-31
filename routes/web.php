<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Pengguna yang membuka halaman utama akan diarahkan ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rute Dashboard utama yang menampilkan halaman pencarian
Route::get('/dashboard', [SearchController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard'); // <-- Diberi nama 'dashboard'

// Grup untuk rute-rute lain yang memerlukan login
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Rute untuk memproses pencarian dan menampilkan hasil
    // URL diubah menjadi /search agar tidak bentrok dengan /dashboard
    Route::get('/search', [SearchController::class, 'search'])->name('search.perform');

    // Rute Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rute Autentikasi
require __DIR__.'/auth.php';