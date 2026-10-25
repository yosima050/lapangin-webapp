<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaMaster;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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

        $totalKasir = Pelanggan::where('role', 'kasir')->where('is_active', true)->count();
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
            'password' => 'required|string|min:8',
            'no_hp' => 'nullable|string|max:20',
        ]);

        $kasir = new Pelanggan();
        $kasir->nama = $validated['nama'];
        $kasir->email = $validated['email'];
        $kasir->password_hash = Hash::make($validated['password']);
        $kasir->role = 'kasir';

        if (array_key_exists('no_hp', $validated)) {
            $kasir->no_hp = $validated['no_hp'] ?: null;
        }

        $kasir->save();

        return redirect()
            ->route('admin.pegawai.index')
            ->with(
                'success',
                "Akun kasir {$kasir->nama} berhasil dibuat."
            );
    }


    /**
     * Mengubah status aktif akun kasir.
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $pegawai = Pelanggan::where('id', $id)
            ->where('role', 'kasir')
            ->firstOrFail();

        $pegawai->is_active = ! $pegawai->is_active;
        $pegawai->save();

        $status = $pegawai->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.pegawai.index')
            ->with('success', "Akun kasir {$pegawai->nama} berhasil {$status}.");
    }


    /**
     * Mengubah role akun pegawai.
     */
    public function updateRole(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,kasir'],
        ]);

        $pegawai = Pelanggan::whereIn('role', ['admin', 'kasir'])
            ->findOrFail($id);

        // Jangan izinkan admin mengubah role akunnya sendiri.
        if ((string) Auth::id() === (string) $pegawai->id) {
            return redirect()->route('admin.pegawai.index')
                ->with('error', 'Role akun yang sedang digunakan tidak dapat diubah.');
        }

        // Jangan sampai sistem kehilangan seluruh akun admin.
        if (
            $pegawai->role === 'admin'
            && $validated['role'] === 'kasir'
            && Pelanggan::where('role', 'admin')->count() <= 1
        ) {
            return redirect()->route('admin.pegawai.index')
                ->with('error', 'Role admin terakhir tidak dapat diubah.');
        }

        $pegawai->role = $validated['role'];
        $pegawai->save();

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Role pegawai berhasil diperbarui.');
    }
}
