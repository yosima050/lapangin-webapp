<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Perlengkapan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckinController extends Controller
{
    /**
     * Tampilkan antarmuka kasir untuk pemindaian Barcode / QR Code (Hardware HID).
     */
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        // Riwayat check-in hari ini
        $recentCheckIns = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
            ->whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', $today)
            ->orderByDesc('checked_in_at')
            ->take(8)
            ->get();

        // Antrean jadwal hari ini yang belum check-in
        $pendingToday = Jadwal::with(['lapangan', 'pelanggan', 'transaksis'])
            ->whereDate('tanggal', $today)
            ->whereNull('checked_in_at')
            ->notCancelled()
            ->orderBy('jam_mulai')
            ->take(8)
            ->get();

        $totalCheckInToday = Jadwal::whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', $today)
            ->count();

        $lapangans = Lapangan::all();

        return view('kasir.checkin', compact(
            'recentCheckIns',
            'pendingToday',
            'totalCheckInToday',
            'lapangans'
        ));
    }

    /**
     * Validasi kode barcode/QR atau token, proses pelunasan onsite jika DP 50%, dan stempel check-in.
     */
    public function process(Request $request): RedirectResponse|JsonResponse
    {
        $code = trim(
            (string) ($request->input('code')
                ?: $request->input('kode_tiket')
                ?: $request->input('token_checkin')
                ?: $request->input('search', ''))
        );

        $expectsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();

        if (empty($code)) {
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Kode barcode / QR atau token tiket tidak boleh kosong.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Kode barcode / QR atau token tiket tidak boleh kosong.');
        }

        try {
            $result = DB::transaction(function () use ($request, $code) {
                // Kunci baris jadwal dengan pesimistic lock untuk mencegah double check-in
                $jadwal = Jadwal::with(['lapangan', 'pelanggan', 'transaksis', 'detailSewaPerlengkapans'])
                    ->where(function ($query) use ($code) {
                        $query->where('kode_qr', $code)
                            ->orWhere('token_manual', strtoupper($code))
                            ->orWhere('token_manual', $code);

                        if (Str::isUuid($code)) {
                            $query->orWhere('id', $code);
                        }
                    })
                    ->lockForUpdate()
                    ->first();

                if (! $jadwal) {
                    return [
                        'status' => 'not_found',
                        'message' => "Tiket dengan kode atau token \"{$code}\" tidak ditemukan dalam database.",
                    ];
                }

                // 1. Cek apakah tiket sudah pernah digunakan
                if ($jadwal->checked_in_at !== null || $jadwal->status === 'selesai') {
                    $waktuCheckIn = $jadwal->checked_in_at
                        ? Carbon::parse($jadwal->checked_in_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB'
                        : 'Sebelumnya';

                    return [
                        'status' => 'duplicate',
                        'message' => "Tiket ini sudah pernah digunakan untuk check-in pada {$waktuCheckIn}.",
                        'jadwal' => $jadwal,
                    ];
                }

                // 2. Cek apakah reservasi dibatalkan atau ditolak
                if ($jadwal->isCancelled()) {
                    return [
                        'status' => 'cancelled',
                        'message' => 'Reservasi ini telah dibatalkan atau ditolak. Tidak dapat melakukan pelunasan atau check-in.',
                        'jadwal' => $jadwal,
                    ];
                }

                // 3. Hitung rincian pelunasan
                $totalHarga = (float) $jadwal->harga_disepakati;
                $terbayar = (float) $jadwal->transaksis->where('status', 'berhasil')->sum('jumlah');
                $sisaTagihan = max(0, $totalHarga - $terbayar);

                $metodePelunasan = strtolower((string) $request->input('metode', 'tunai'));
                $uangDiterima = (float) ($request->input('cash_received') ?: $sisaTagihan);

                // Jika masih ada sisa tagihan (misal status baru DP 50%), catat pelunasan kasir
                if ($sisaTagihan > 0) {
                    $orderIdPelunasan = 'LPNG-KSR-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                    Transaksi::create([
                        'jadwal_id' => $jadwal->id,
                        'jenis' => 'pelunasan',
                        'jumlah' => $sisaTagihan,
                        'metode' => in_array($metodePelunasan, ['qris', 'transfer', 'online'], true) ? 'online' : 'tunai',
                        'status' => 'berhasil',
                        'dibayar_pada' => now(),
                        'order_id' => $orderIdPelunasan,
                        'referensi_midtrans' => $orderIdPelunasan,
                    ]);
                }

                // 4. Update status kehadiran & status pembayaran
                $jadwal->update([
                    'checked_in_at' => now(),
                    'status' => 'terverifikasi',
                    'status_pembayaran' => ($sisaTagihan > 0 || $jadwal->status_pembayaran === 'dp_terverifikasi')
                        ? 'lunas_kasir'
                        : $jadwal->status_pembayaran,
                    'kasir_id' => $request->user()?->getAuthIdentifier(),
                    'catatan_kasir' => $request->input('catatan', $jadwal->catatan_kasir),
                ]);

                $kembalian = max(0, $uangDiterima - $sisaTagihan);

                return [
                    'status' => 'success',
                    'message' => 'Check-in berhasil divalidasi. Mengalihkan ke cetak struk thermal...',
                    'jadwal' => $jadwal->fresh(['lapangan', 'pelanggan', 'transaksis']),
                    'sisa_tagihan' => $sisaTagihan,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                ];
            });

            if ($result['status'] !== 'success') {
                if ($expectsJson) {
                    return response()->json([
                        'success' => false,
                        'status' => $result['status'],
                        'message' => $result['message'],
                        'jadwal' => $result['jadwal'] ?? null,
                    ], 422);
                }

                return redirect()->back()->with('error', $result['message']);
            }

            // Simpan info kembalian ke session untuk ditampilkan di struk
            session()->flash('cash_received', $result['uang_diterima']);
            session()->flash('cash_change', $result['kembalian']);
            session()->flash('success', 'Check-in berhasil tervalidasi!');

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'status' => 'success',
                    'message' => $result['message'],
                    'redirect_url' => route('kasir.struk', $result['jadwal']->id),
                    'jadwal' => $result['jadwal'],
                ]);
            }

            return redirect()->route('kasir.struk', $result['jadwal']->id);
        } catch (\Throwable $e) {
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan sistem saat memproses check-in: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Render view struk kasir thermal 58mm / 80mm.
     */
    public function struk(Request $request, string $id): View
    {
        $jadwal = Jadwal::with([
            'lapangan',
            'pelanggan',
            'transaksis',
            'kasir',
            'detailSewaPerlengkapans',
        ])
        ->where('id', $id)
        ->orWhere('kode_qr', $id)
        ->orWhere('token_manual', strtoupper($id))
        ->firstOrFail();

        $totalHarga = (float) $jadwal->harga_disepakati;

        // DP terbayar (online / Midtrans)
        $dpTransaksi = $jadwal->transaksis
            ->whereIn('jenis', ['dp', 'full'])
            ->where('status', 'berhasil')
            ->first();

        $dpTerbayar = (float) ($dpTransaksi?->jumlah ?? 0);

        // Pelunasan kasir (onsite tunai/QRIS)
        $pelunasanTransaksi = $jadwal->transaksis
            ->where('jenis', 'pelunasan')
            ->where('status', 'berhasil')
            ->first();

        $pelunasanBayar = (float) ($pelunasanTransaksi?->jumlah ?? 0);

        $totalTerbayar = (float) $jadwal->transaksis->where('status', 'berhasil')->sum('jumlah');
        $sisaTagihan = max(0, $totalHarga - $totalTerbayar);

        $namaPenyewa = $jadwal->nama_tamu ?: ($jadwal->pelanggan?->nama ?? 'Tamu Lapangan');
        $noWaPenyewa = $jadwal->no_wa_tamu ?: ($jadwal->pelanggan?->no_hp ?? '-');

        $namaKasir = $jadwal->kasir?->nama ?? auth()->user()?->nama ?? 'Kasir LapangIn';

        $uangDiterima = session('cash_received', $pelunasanBayar);
        $kembalian = session('cash_change', 0);

        return view('kasir.struk', compact(
            'jadwal',
            'totalHarga',
            'dpTerbayar',
            'pelunasanBayar',
            'totalTerbayar',
            'sisaTagihan',
            'namaPenyewa',
            'noWaPenyewa',
            'namaKasir',
            'uangDiterima',
            'kembalian'
        ));
    }
}
