<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\HargaMasterController;
use App\Http\Controllers\Admin\LapanganController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Pelanggan\RiwayatController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'index'])->name('home');

Route::get('/lapangan/{id}', [KatalogController::class, 'show'])->name('katalog.show');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/jadwal', [KatalogController::class, 'index'])->name('jadwal.index');

Route::get('/dashboard', function () {
    $role = auth()->user()?->role;
    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($role === 'kasir') {
        return redirect()->route('kasir.index');
    }
    return redirect()->route('pelanggan.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
});

// Halaman Admin
Route::middleware(['auth', 'IsAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('lapangan', LapanganController::class);
    Route::resource('harga', HargaMasterController::class);
    Route::get('/pegawai', [\App\Http\Controllers\Admin\PegawaiController::class, 'index'])->name('pegawai.index');
    Route::post('/pegawai', [\App\Http\Controllers\Admin\PegawaiController::class, 'store'])->name('pegawai.store');
    Route::get('/custom-price', [\App\Http\Controllers\Admin\PegawaiController::class, 'index'])->name('custom-price');
    Route::get('/laporan', [AdminDashboardController::class, 'laporan'])->name('laporan.index');
});

// Halaman Kasir
Route::middleware(['auth', 'IsKasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Kasir\KasirController::class, 'index'])->name('index');
    Route::post('/checkin/{id}', [\App\Http\Controllers\Kasir\KasirController::class, 'checkin'])->name('checkin');
    Route::get('/jadwal', [\App\Http\Controllers\Kasir\KasirController::class, 'jadwal'])->name('jadwal');
    Route::post('/walkin', [\App\Http\Controllers\Kasir\KasirController::class, 'storeWalkin'])->name('walkin.store');
    Route::get('/reschedule', [\App\Http\Controllers\Kasir\KasirController::class, 'reschedule'])->name('reschedule');
    Route::post('/reschedule/{id}/approve', [\App\Http\Controllers\Kasir\KasirController::class, 'approveReschedule'])->name('reschedule.approve');
    Route::post('/reschedule/{id}/reject', [\App\Http\Controllers\Kasir\KasirController::class, 'rejectReschedule'])->name('reschedule.reject');
    Route::get('/transaksi', [\App\Http\Controllers\Kasir\KasirController::class, 'transaksi'])->name('transaksi');
});

// Halaman Pelanggan
Route::middleware(['auth', 'IsPelanggan'])->group(function () {
    Route::get('/pelanggan', function () {
        return view('Pelanggan.index');
    })->name('pelanggan.dashboard');

    Route::get('/pelanggan/riwayat', [RiwayatController::class, 'index'])
        ->name('pelanggan.riwayat');
    Route::get('/pelanggan/booking', [RiwayatController::class, 'index'])
        ->name('pelanggan.booking.index');
});

require __DIR__.'/auth.php';
