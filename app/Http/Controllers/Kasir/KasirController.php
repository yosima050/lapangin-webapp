<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Tampilan Check-in & Validasi QR E-Ticket (Image 1)
     */
    public function index(Request $request): View
    {
        $lapangans = Lapangan::all();
        $code = $request->input('code');

        $activeBooking = null;
        if (!empty($code)) {
            $activeBooking = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
                ->where(function ($query) use ($code) {
                    $query->where('kode_qr', $code)
                        ->orWhere('token_manual', $code)
                        ->orWhere('id', $code);
                })
                ->first();
        }

        // Fallback jika kode tidak disuplai atau tidak ketemu, ambil jadwal pertama yang butuh perhatian hari ini
        if (!$activeBooking) {
            $activeBooking = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
                ->whereDate('tanggal', now()->toDateString())
                ->where('status_pembayaran', '!=', 'lunas_kasir')
                ->orderBy('jam_mulai')
                ->first();

            if (!$activeBooking) {
                $activeBooking = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
                    ->latest('created_at')
                    ->first();
            }
        }

        // Kalkulasi rincian pembayaran dinamis dari tabel transaksis
        $totalHarga = $activeBooking ? (float) $activeBooking->harga_disepakati : 0;
        $dpPaid = $activeBooking ? (float) $activeBooking->transaksis->where('status', 'berhasil')->sum('jumlah') : 0;
        $sisaTagihan = max(0, $totalHarga - $dpPaid);

        // Antrean reservasi hari ini dari database
        $antreanHariIni = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('jam_mulai')
            ->get();

        if ($antreanHariIni->isEmpty()) {
            $antreanHariIni = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
                ->orderBy('jam_mulai')
                ->take(6)
                ->get();
        }

        $totalCapacitySlots = max(12, $antreanHariIni->count() + 4);
        $occupiedSlots = $antreanHariIni->count();
        $occupancyRate = round(($occupiedSlots / $totalCapacitySlots) * 100);

        return view('Kasir.index', compact(
            'lapangans',
            'activeBooking',
            'totalHarga',
            'dpPaid',
            'sisaTagihan',
            'antreanHariIni',
            'occupancyRate',
            'occupiedSlots',
            'totalCapacitySlots',
            'code'
        ));
    }

    /**
     * Konfirmasi Pelunasan & Validasi Check-in di Meja Kasir
     */
    public function checkin(Request $request, string $id): RedirectResponse
    {
        $jadwal = Jadwal::with('transaksis')->findOrFail($id);

        $dpPaid = (float) $jadwal->transaksis->where('status', 'berhasil')->sum('jumlah');
        $sisaTagihan = max(0, (float)$jadwal->harga_disepakati - $dpPaid);

        $metode = $request->input('metode', 'tunai');
        $jumlahBayar = (float) $request->input('jumlah', $sisaTagihan);

        // Catat transaksi pelunasan di PostgreSQL bila masih ada tagihan
        if ($sisaTagihan > 0 && $jumlahBayar > 0) {
            Transaksi::create([
                'jadwal_id' => $jadwal->id,
                'jenis' => 'pelunasan',
                'jumlah' => $jumlahBayar,
                'metode' => $metode === 'cash' || $metode === 'tunai' ? 'tunai' : 'online',
                'status' => 'berhasil',
                'dibayar_pada' => now(),
            ]);
        }

        // Update status jadwal menjadi lunas_kasir dan terverifikasi
        $jadwal->update([
            'status_pembayaran' => 'lunas_kasir',
            'status' => 'terverifikasi',
            'catatan_kasir' => $request->input('catatan', $jadwal->catatan_kasir),
        ]);

        $namaPenyewa = $jadwal->pelanggan->nama ?? $jadwal->nama_tamu ?? 'Penyewa';

        return redirect()->route('kasir.index', ['code' => $jadwal->kode_qr ?? $jadwal->id])
            ->with('success', "Pelunasan sebesar Rp " . number_format($jumlahBayar, 0, ',', '.') . " untuk {$namaPenyewa} berhasil dicatat! Status booking telah lunas.");
    }

    /**
     * Tampilan Jadwal Lapangan & Booking Walk-in (Image 4)
     */
    public function jadwal(Request $request): View
    {
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedCourtId = $request->input('lapangan_id', 'all');

        $lapangans = Lapangan::with('hargaMasters')->get();

        $jadwalsQuery = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
            ->whereDate('tanggal', $selectedDate);

        if ($selectedCourtId !== 'all' && !empty($selectedCourtId)) {
            $jadwalsQuery->where('lapangan_id', $selectedCourtId);
        }

        $jadwals = $jadwalsQuery->orderBy('jam_mulai')->get();

        // Jika hari ini belum banyak jadwal, ambil semua jadwal terdaftar agar kalender kasir selalu terisi data riil
        if ($jadwals->isEmpty()) {
            $jadwals = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
                ->orderBy('tanggal')
                ->orderBy('jam_mulai')
                ->take(6)
                ->get();
        }

        $totalDailySlots = 14;
        $occupancyCount = $jadwals->count();
        $occupancyRate = round(($occupancyCount / $totalDailySlots) * 100);

        // Ambil daftar jam yang masih kosong untuk lapangan aktif
        $bookedHours = $jadwals->map(function ($j) {
            return substr($j->jam_mulai, 0, 5);
        })->toArray();

        $availableSlots = [];
        $operatingHours = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
        foreach ($operatingHours as $h) {
            if (!in_array($h, $bookedHours)) {
                $availableSlots[] = $h;
            }
        }

        return view('Kasir.jadwal', compact(
            'lapangans',
            'jadwals',
            'selectedDate',
            'selectedCourtId',
            'occupancyRate',
            'occupancyCount',
            'totalDailySlots',
            'availableSlots'
        ));
    }

    /**
     * Simpan Booking Walk-in Baru dari Meja Kasir
     */
    public function storeWalkin(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_tamu' => 'required|string|max:100',
            'no_wa_tamu' => 'required|string|max:20',
            'lapangan_id' => 'required|exists:lapangans,id',
            'jam_mulai' => 'required',
            'durasi' => 'required|integer|min:1|max:4',
            'payType' => 'required|in:full,dp',
        ]);

        $tanggal = $request->input('tanggal', now()->toDateString());
        $jamMulai = Carbon::parse($request->jam_mulai)->format('H:i:00');
        $durasi = (int) $request->durasi;
        $jamSelesai = Carbon::parse($request->jam_mulai)->addHours($durasi)->format('H:i:00');

        $lapangan = Lapangan::with('hargaMasters')->findOrFail($request->lapangan_id);
        
        // Hitung harga per jam dari HargaMaster
        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [0, 6]);

        $customPrice = $lapangan->hargaMasters->firstWhere('tanggal_khusus', $tanggal);
        if ($customPrice) {
            $ratePerHour = (float) $customPrice->harga;
        } else {
            $defaultPrice = $lapangan->hargaMasters
                ->whereNull('tanggal_khusus')
                ->where('jenis_hari', $isWeekend ? 'weekend' : 'weekday')
                ->first();
            $ratePerHour = $defaultPrice ? (float) $defaultPrice->harga : 150000;
        }

        $totalTarif = $ratePerHour * $durasi;

        $jadwal = Jadwal::create([
            'lapangan_id' => $lapangan->id,
            'nama_tamu' => $request->nama_tamu,
            'no_wa_tamu' => $request->no_wa_tamu,
            'tanggal' => $tanggal,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'harga_disepakati' => $totalTarif,
            'biaya_layanan' => 0,
            'status_pembayaran' => $request->payType === 'full' ? 'lunas_kasir' : 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'kode_qr' => 'LPGN-' . date('Ymd') . '-' . rand(1000, 9999),
            'token_manual' => strtoupper(Str::random(6)),
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'catatan_kasir' => 'Booking Walk-in Meja Kasir POS',
            'created_at' => now(),
        ]);

        $bayarNominal = $request->payType === 'full' ? $totalTarif : ($totalTarif * 0.5);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => $request->payType === 'full' ? 'full' : 'dp',
            'jumlah' => $bayarNominal,
            'metode' => 'tunai',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        return redirect()->route('kasir.jadwal', ['tanggal' => $tanggal, 'lapangan_id' => $lapangan->id])
            ->with('success', "Booking walk-in untuk {$jadwal->nama_tamu} di {$lapangan->nama} berhasil disimpan dengan kode {$jadwal->kode_qr}!");
    }

    /**
     * Tampilan Kelola Pengajuan Reschedule (Image 5)
     */
    public function reschedule(Request $request): View
    {
        $lapangans = Lapangan::with('hargaMasters')->get();

        // Ambil data pengajuan reschedule aktual dari database
        $reschedules = Reschedule::with(['jadwal.lapangan.hargaMasters', 'jadwal.pelanggan', 'jadwal.transaksis'])
            ->latest('created_at')
            ->get();

        $urgentCount = Reschedule::where('status', 'diajukan')->count();
        $approvedCount = Reschedule::where('status', 'disetujui')->count();
        $rejectedCount = Reschedule::where('status', 'ditolak')->count();
        $totalPengajuan = $reschedules->count();

        // Ambil permohonan yang sedang dipilih untuk diinspeksi
        $selectedId = $request->input('id');
        $selectedReschedule = $selectedId
            ? $reschedules->firstWhere('id', $selectedId)
            : $reschedules->where('status', 'diajukan')->first() ?? $reschedules->first();

        // Cek konflik ketersediaan slot usulan baru di database
        $isSlotAvailable = true;
        $tarifSesiBaru = 0;
        $selisihBiaya = 0;

        if ($selectedReschedule) {
            $conflictCount = Jadwal::where('lapangan_id', $selectedReschedule->jadwal?->lapangan_id)
                ->where('id', '!=', $selectedReschedule->jadwal_id)
                ->whereDate('tanggal', $selectedReschedule->tanggal_baru)
                ->where(function ($q) use ($selectedReschedule) {
                    $q->whereBetween('jam_mulai', [$selectedReschedule->jam_mulai_baru, $selectedReschedule->jam_selesai_baru])
                      ->orWhereBetween('jam_selesai', [$selectedReschedule->jam_mulai_baru, $selectedReschedule->jam_selesai_baru]);
                })
                ->count();

            $isSlotAvailable = ($conflictCount === 0);

            // Hitung tarif sesi baru dari tabel harga_masters
            $court = $selectedReschedule->jadwal?->lapangan;
            if ($court) {
                $newDayOfWeek = Carbon::parse($selectedReschedule->tanggal_baru)->dayOfWeek;
                $isNewWeekend = in_array($newDayOfWeek, [0, 6]);

                $customPrice = $court->hargaMasters->firstWhere('tanggal_khusus', $selectedReschedule->tanggal_baru->toDateString());
                if ($customPrice) {
                    $ratePerHour = (float) $customPrice->harga;
                } else {
                    $regular = $court->hargaMasters
                        ->whereNull('tanggal_khusus')
                        ->where('jenis_hari', $isNewWeekend ? 'weekend' : 'weekday')
                        ->first();
                    $ratePerHour = $regular ? (float) $regular->harga : (float)$selectedReschedule->jadwal?->harga_disepakati;
                }

                $durasiJam = max(1, Carbon::parse($selectedReschedule->jam_mulai_baru)->diffInHours(Carbon::parse($selectedReschedule->jam_selesai_baru)));
                $tarifSesiBaru = $ratePerHour * $durasiJam;
            } else {
                $tarifSesiBaru = (float) ($selectedReschedule->jadwal?->harga_disepakati ?? 0);
            }

            $selisihBiaya = $tarifSesiBaru - (float) ($selectedReschedule->jadwal?->harga_disepakati ?? 0);
        }

        // Hitung total slot pengganti siap pakai hari ini
        $bookedTodayCount = Jadwal::whereDate('tanggal', now()->toDateString())->count();
        $totalDailySlots = max(0, ($lapangans->count() * 12) - $bookedTodayCount);

        return view('Kasir.reschedule', compact(
            'lapangans',
            'reschedules',
            'selectedReschedule',
            'urgentCount',
            'approvedCount',
            'rejectedCount',
            'totalPengajuan',
            'isSlotAvailable',
            'tarifSesiBaru',
            'selisihBiaya',
            'totalDailySlots'
        ));
    }

    /**
     * Setujui Pengajuan Reschedule Jadwal
     */
    public function approveReschedule(Request $request, string $id): RedirectResponse
    {
        $reschedule = Reschedule::with('jadwal')->findOrFail($id);

        $reschedule->update([
            'status' => 'disetujui',
        ]);

        $reschedule->jadwal->update([
            'tanggal' => $reschedule->tanggal_baru,
            'jam_mulai' => $reschedule->jam_mulai_baru,
            'jam_selesai' => $reschedule->jam_selesai_baru,
            'catatan_kasir' => $request->input('catatan', 'Reschedule disetujui kasir pada ' . now()->format('d/m/Y H:i')),
        ]);

        $namaPenyewa = $reschedule->jadwal->pelanggan->nama ?? $reschedule->jadwal->nama_tamu ?? 'Penyewa';

        return redirect()->route('kasir.reschedule')
            ->with('success', "Permohonan reschedule untuk {$namaPenyewa} BERHASIL DISETUJUI! Jadwal otomatis diperbarui ke tanggal " . $reschedule->tanggal_baru->format('d M Y') . ".");
    }

    /**
     * Tolak Pengajuan Reschedule Jadwal
     */
    public function rejectReschedule(Request $request, string $id): RedirectResponse
    {
        $reschedule = Reschedule::with('jadwal')->findOrFail($id);

        $reschedule->update([
            'status' => 'ditolak',
        ]);

        $namaPenyewa = $reschedule->jadwal->pelanggan->nama ?? $reschedule->jadwal->nama_tamu ?? 'Penyewa';

        return redirect()->route('kasir.reschedule')
            ->with('warning', "Permohonan reschedule untuk {$namaPenyewa} DITOLAK. Jadwal reservasi lama tetap dipertahankan.");
    }

    /**
     * Tampilan Transaksi & Tutup Shift
     */
    public function transaksi(): View
    {
        $lapangans = Lapangan::all();

        $transaksis = Transaksi::with(['jadwal.lapangan', 'jadwal.pelanggan'])
            ->latest('dibayar_pada')
            ->get();

        $totalTunai = Transaksi::where('metode', 'tunai')->where('status', 'berhasil')->sum('jumlah');
        $totalNonTunai = Transaksi::where('metode', 'online')->where('status', 'berhasil')->sum('jumlah');
        $totalOmset = $totalTunai + $totalNonTunai;
        $totalTransaksi = $transaksis->count();

        return view('Kasir.transaksi', compact(
            'lapangans',
            'transaksis',
            'totalTunai',
            'totalNonTunai',
            'totalOmset',
            'totalTransaksi'
        ));
    }
}

