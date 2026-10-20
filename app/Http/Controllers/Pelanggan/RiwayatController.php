<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Transaksi;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        Jadwal::autoCancelExpiredPendingBookings();

        $pelanggan = $request->user();

        // Otomatis sinkronisasi transaksi yang masih pending ke Midtrans API
        if ($pelanggan && class_exists(PaymentService::class)) {
            $paymentService = app(PaymentService::class);
            $pendingTransaksis = Transaksi::whereHas('jadwal', function ($q) use ($pelanggan) {
                $q->where('pelanggan_id', $pelanggan->id);
            })->where('status', 'pending')
              ->whereNotNull('order_id')
              ->get();

            foreach ($pendingTransaksis as $trx) {
                $paymentService->syncStatus($trx);
            }
        }

        $jadwals = $pelanggan ? $pelanggan->jadwals()
            ->with(['lapangan', 'transaksis'])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->paginate(10) : collect();

        return view('Pelanggan.riwayat', compact('jadwals'));
    }
}
