<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalLapangan = Lapangan::count();
        $totalHargaMaster = HargaMaster::count();
        $totalPelanggan = Pelanggan::where('role', 'pelanggan')->count();
        $totalBookingHariIni = Jadwal::whereDate('tanggal', now()->toDateString())->count();

        $lapangansTerbaru = Lapangan::with('hargaMasters')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('Admin.index', compact(
            'totalLapangan',
            'totalHargaMaster',
            'totalPelanggan',
            'totalBookingHariIni',
            'lapangansTerbaru'
        ));
    }

    public function laporan(): View
    {
        $totalPendapatan = \App\Models\Transaksi::where('status', 'settlement')->sum('jumlah');
        $transaksiTerbaru = \App\Models\Transaksi::with(['jadwal.pelanggan', 'jadwal.lapangan'])
            ->orderByDesc('dibayar_pada')
            ->take(15)
            ->get();
        $totalTransaksiSukses = \App\Models\Transaksi::where('status', 'settlement')->count();

        return view('Admin.laporan.index', compact(
            'totalPendapatan',
            'transaksiTerbaru',
            'totalTransaksiSukses'
        ));
    }
}
