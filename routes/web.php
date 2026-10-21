<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\HargaMasterController;
use App\Http\Controllers\Admin\LapanganController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\Pelanggan\RiwayatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'index'])->name('home');
Route::get('/lapangan/{id}', [KatalogController::class, 'show'])->name('katalog.show');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/jadwal', [KatalogController::class, 'index'])->name('jadwal.index');

Route::get('/dashboard', function () {
    $role = auth()->user()->role;

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
    Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/status/{orderId}', [CheckoutController::class, 'status'])->name('checkout.status');
    Route::get('/checkout/check-status/{orderId}', [CheckoutController::class, 'checkStatus'])->name('checkout.checkStatus');
    Route::post('/checkout/expire/{id}', [CheckoutController::class, 'expire'])->name('checkout.expire');
});

// Halaman Admin
Route::middleware(['auth', 'IsAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('lapangan', LapanganController::class);
    Route::resource('harga', HargaMasterController::class);

    // Halaman transaksi admin
    Route::get('/transaksi', [TransaksiController::class, 'index'])
        ->name('transaksi.index');

    Route::get('/pegawai', [PegawaiController::class, 'index'])
        ->name('pegawai.index');

    Route::post('/pegawai', [PegawaiController::class, 'store'])
        ->name('pegawai.store');

    Route::get('/custom-price', [PegawaiController::class, 'index'])
        ->name('custom-price');

    Route::get('/laporan', [AdminDashboardController::class, 'laporan'])
        ->name('laporan.index');
});

// Halaman Kasir
Route::middleware(['auth', 'IsKasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])
        ->name('index');

    Route::post('/checkin/{id}', [KasirController::class, 'checkin'])
        ->name('checkin');

    Route::get('/jadwal', [KasirController::class, 'jadwal'])
        ->name('jadwal');

    Route::post('/walkin', [KasirController::class, 'storeWalkin'])
        ->name('walkin.store');

    Route::get('/reschedule', [KasirController::class, 'reschedule'])
        ->name('reschedule');

    Route::post('/reschedule/{id}/approve', [KasirController::class, 'approveReschedule'])
        ->name('reschedule.approve');

    Route::post('/reschedule/{id}/reject', [KasirController::class, 'rejectReschedule'])
        ->name('reschedule.reject');

    Route::get('/transaksi', [KasirController::class, 'transaksi'])
        ->name('transaksi');
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

// Midtrans Webhook Notification Handler
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook');
Route::post('/api/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook.api');
Route::post('/midtrans/notification', [MidtransCallbackController::class, 'handle'])
    ->name('midtrans.notification');
Route::post('/api/midtrans/notification', [MidtransCallbackController::class, 'handle'])
    ->name('midtrans.notification.api');
