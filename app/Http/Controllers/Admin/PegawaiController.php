<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaMaster;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    /**
     * Tampilan Kelola Staf Kasir & Dynamic Pricing Hari Libur (Image 2)
     */
    public function index(Request $request): View
    {
        // 1. Ambil daftar pegawai dan kasir POS riil dari database
        $pegawais = Pelanggan::whereIn('role', ['admin', 'kasir'])
            ->orderBy('role')
            ->orderBy('nama')
            ->get();

        $totalKasir = Pelanggan::where('role', 'kasir')->count();
        $totalAdmin = Pelanggan::where('role', 'admin')->count();

        // 2. Ambil data penyesuaian tarif dinamis tanggal merah riil dari database
        $customPrices = HargaMaster::with('lapangan')
            ->whereNotNull('tanggal_khusus')
            ->orderBy('tanggal_khusus')
            ->get();

        // 3. Kalender dinamis bulan ini dengan tarif riil dari database
        $now = Carbon::now();
        $daysInMonth = $now->daysInMonth;
        $startDayOfWeek = $now->copy()->startOfMonth()->dayOfWeek; // 0 = Minggu

        $calendarDays = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $now->copy()->day($d);
            $dateStr = $date->toDateString();
            $isWeekend = in_array($date->dayOfWeek, [0, 6]);
            $isToday = $dateStr === now()->toDateString();

            // Cek apakah ada tarif khusus di database untuk tanggal ini
            $custom = $customPrices->firstWhere('tanggal_khusus', $dateStr);

            $rate = $custom ? (float)$custom->harga : ($isWeekend ? 150000 : 120000);

            $calendarDays[] = [
                'day' => $d,
                'date' => $dateStr,
                'isToday' => $isToday,
                'isWeekend' => $isWeekend,
                'isHoliday' => (bool) $custom,
                'rateText' => round($rate / 1000) . 'k/j',
                'custom' => $custom,
            ];
        }

        return view('Admin.pegawai.index', compact(
            'pegawais',
            'totalKasir',
            'totalAdmin',
            'customPrices',
            'calendarDays',
            'startDayOfWeek'
        ));
    }

    /**
     * Simpan Akun Kasir POS Baru ke Database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:pelanggans,email',
            'password' => 'nullable|string|min:6',
            'no_hp' => 'nullable|string|max:20',
        ]);

        $kasir = Pelanggan::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? ('0812' . rand(10000000, 99999999)),
            'password_hash' => Hash::make($request->input('password', 'ArenaGor2025#')),
            'role' => 'kasir',
        ]);

        return redirect()->route('admin.pegawai.index')
            ->with('success', "Akun kasir untuk {$kasir->nama} ({$kasir->email}) berhasil dibuat dan tersimpan ke database!");
    }
}
