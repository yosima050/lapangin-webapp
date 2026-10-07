<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lapangan/{id}', function ($id) {
    return view('lapangan.show', ['id' => $id]);
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Halaman Admin
Route::middleware(['auth', 'IsAdmin'])->group(function () {
    Route::get('/admin', function () {
        return view('Admin.index');
    });
});

// Halaman Kasir
Route::middleware(['auth', 'IsKasir'])->group(function () {
    Route::get('/kasir', function () {
        return view('Kasir.index');
    });
});

// Halaman Pelanggan
Route::middleware(['auth', 'IsPelanggan'])->group(function () {
    Route::get('/pelanggan', function () {
        return view('Pelanggan.index');
    })->name('pelanggan.dashboard');
});

require __DIR__.'/auth.php';
