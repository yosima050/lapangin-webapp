<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route Group Middleware
Route::middleware(['auth', 'IsAdmin'])->group(function () {
    Route::get('/admin', function () {
        return "Halaman Admin";
    });
});

Route::middleware(['auth', 'IsKasir'])->group(function () {
    Route::get('/kasir', function () {
        return "Halaman Kasir";
    });
});

Route::middleware(['auth', 'IsPelanggan'])->group(function () {
    Route::get('/pelanggan', function () {
        return "Halaman Pelanggan";
    });
});

require __DIR__.'/auth.php';