<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLaporanController;
use App\Http\Controllers\Admin\HargaMasterController;
use App\Http\Controllers\Admin\LapanganController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\PerlengkapanController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\Pelanggan\RiwayatController;
use App\Http\Controllers\Pelanggan\BookingRescheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\HargaKhususController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Kasir\CheckinController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.landing');
})->name('home');
Route::get('/lapangan/{id}', [KatalogController::class, 'show'])->name('katalog.show');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/jadwal', [KatalogController::class, 'index'])->name('jadwal.index');

// Informasi Publik & Bantuan
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::get('/bantuan', [PageController::class, 'bantuan'])->name('bantuan');
Route::get('/kebijakan-reschedule', [PageController::class, 'kebijakanReschedule'])->name('kebijakan.reschedule');
Route::get('/syarat-ketentuan', [PageController::class, 'syaratKetentuan'])->name('syarat.ketentuan');
Route::get('/kebijakan-privasi', [PageController::class, 'kebijakanPrivasi'])->name('kebijakan.privasi');

Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($role === 'kasir') {
        return redirect()->route('kasir.index');
    }

    return redirect()->route('katalog.index');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Checkout Booking (Dapat diakses oleh Pelanggan terdaftar maupun Tamu Walk-in)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
Route::get('/checkout/success/{orderId}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/status/{orderId}', [CheckoutController::class, 'status'])->name('checkout.status');
Route::get('/checkout/check-status/{orderId}', [CheckoutController::class, 'checkStatus'])->name('checkout.checkStatus');
Route::post('/checkout/expire/{id}', [CheckoutController::class, 'expire'])->name('checkout.expire');

// Halaman Admin
Route::middleware(['auth', 'IsAdmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('lapangan', LapanganController::class);
        Route::resource('harga', HargaMasterController::class);
        Route::resource('perlengkapan', PerlengkapanController::class);
        Route::resource('lapangan', LapanganController::class);
        Route::resource('harga', HargaMasterController::class);


        // Transaksi admin
        Route::get('/transaksi', [TransaksiController::class, 'index'])
            ->name('transaksi.index');

        // Manajemen pegawai dan akun kasir
        Route::get('/pegawai', [PegawaiController::class, 'index'])
            ->name('pegawai.index');

        Route::post('/pegawai', [PegawaiController::class, 'store'])
            ->name('pegawai.store');

        Route::patch('/pegawai/{id}/status', [PegawaiController::class, 'toggleStatus'])
            ->name('pegawai.toggle-status');

        // Manajemen harga khusus
        Route::get('/harga-khusus', [HargaKhususController::class, 'index'])
            ->name('harga-khusus.index');

        Route::post('/harga-khusus', [HargaKhususController::class, 'store'])
            ->name('harga-khusus.store');

        Route::patch('/harga-khusus/{id}', [HargaKhususController::class, 'update'])
            ->name('harga-khusus.update');

        Route::patch('/harga-khusus/{id}/status', [HargaKhususController::class, 'toggleStatus'])
            ->name('harga-khusus.toggle-status');

        Route::delete('/harga-khusus/{id}', [HargaKhususController::class, 'destroy'])
            ->name('harga-khusus.destroy');

        // Laporan admin
        Route::get('/laporan', [AdminLaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/export-pdf', [AdminLaporanController::class, 'exportPdf'])
            ->name('laporan.exportPdf');

        // Alias lama, dipertahankan sementara untuk kompatibilitas.
        Route::get('/custom-price', [HargaKhususController::class, 'index'])
            ->name('custom-price');

        Route::patch('/pegawai/{id}/role', [\App\Http\Controllers\Admin\PegawaiController::class,'updateRole',])
            ->name('pegawai.update-role');
    });

// Halaman Kasir
Route::middleware(['auth', 'IsKasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])
        ->name('index');

    // Pemindai Barcode / QR Code Hardware (HID) & Cetak Struk Thermal
    Route::get('/checkin', [CheckinController::class, 'index'])
        ->name('checkin.index');

    Route::post('/checkin/process', [CheckinController::class, 'process'])
        ->name('checkin.process');

    Route::get('/struk/{id}', [CheckinController::class, 'struk'])
        ->name('struk');

    // Pemindai Kamera QR Code & Lookup Tiket (Sprint 4)
    Route::get('/scan', [KasirController::class, 'scan'])
        ->name('scan');

    Route::get('/search', [KasirController::class, 'searchTickets'])
        ->name('search');

    Route::get('/ticket/lookup', [KasirController::class, 'lookupTicket'])
        ->name('ticket.lookup');

    Route::post('/ticket/lookup', [KasirController::class, 'lookupTicket'])
        ->name('ticket.lookup.post');

    Route::get('/ticket/{code}', [KasirController::class, 'lookupTicket'])
        ->name('ticket.detail');

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

    // Reschedule & Auto-Penalty Cancellation (Sprint 5)
    Route::get('/pelanggan/booking/{id}/reschedule', [BookingRescheduleController::class, 'create'])
        ->name('pelanggan.booking.reschedule');

    Route::get('/pelanggan/booking/{id}/reschedule/slots', [BookingRescheduleController::class, 'checkSlots'])
        ->name('pelanggan.booking.reschedule.slots');

    Route::post('/pelanggan/booking/{id}/reschedule', [BookingRescheduleController::class, 'store'])
        ->name('pelanggan.booking.reschedule.store');

    Route::get('/pelanggan/booking/{id}/cancel-preview', [BookingRescheduleController::class, 'cancelPreview'])
        ->name('pelanggan.booking.cancel.preview');

    Route::post('/pelanggan/booking/{id}/cancel', [BookingRescheduleController::class, 'cancel'])
        ->name('pelanggan.booking.cancel');
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
