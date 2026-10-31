<?php

namespace App\Http\Controllers;

use App\Jobs\BatalBookingOtomatis;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Transaksi;
use App\Services\GuestAccountProvisioner;
use App\Services\PaymentService;
use App\Models\HargaKhusus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman checkout dan rincian harga sewa lapangan.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        // 1. Pemesanan online hanya dapat dilakukan oleh pelanggan yang memiliki akun
        if (! Auth::check()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->route('register')->with('status', 'Silakan daftar akun terlebih dahulu atau masuk untuk melakukan pemesanan.');
        }

        Jadwal::autoCancelExpiredPendingBookings();

        $data = $request->validate([
            'lapangan_id' => 'required|uuid|exists:lapangans,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'durasi' => 'required|integer|min:1|max:4',
            'tipe_bayar' => 'nullable|string|in:dp,full',
        ]);

        // 2. Validasi slot waktu: jika hari ini, jam mulai tidak boleh sudah lewat dari jam sekarang
        $now = Carbon::now('Asia/Jakarta');
        $isToday = $data['tanggal'] === $now->toDateString();
        $currentTime = $now->format('H:i');

        if ($data['tanggal'] < $now->toDateString() || ($isToday && $data['jam_mulai'] <= $currentTime)) {
            return redirect()->route('katalog.show', ['id' => $data['lapangan_id'], 'tanggal' => $data['tanggal']])
                ->with('error', 'Jam sewa yang dipilih (' . $data['jam_mulai'] . ' WIB) sudah lewat dari waktu saat ini. Silakan pilih slot jam yang masih tersedia.');
        }

        $tipeBayar = $request->input('tipe_bayar', 'dp');
        if (! in_array($tipeBayar, ['dp', 'full'], true)) {
            $tipeBayar = 'dp';
        }

        $pelanggan = Auth::user();

        $details = $this->calculateBooking(
            $data['lapangan_id'],
            $data['tanggal'],
            $data['jam_mulai'],
            (int) $data['durasi'],
            $pelanggan?->id
        );

        $totalHarga = $details['totalHarga'];
        $nominalDp = round($totalHarga * 0.5);

        if ($tipeBayar === 'full') {
            $nominalBayar = (float) $totalHarga;
            $sisaPembayaran = 0.0;
            $persenBayar = 100;
        } else {
            $nominalBayar = (float) $nominalDp;
            $sisaPembayaran = (float) ($totalHarga - $nominalDp);
            $persenBayar = 50;
        }

        $selesaiFormatted = ($details['selesai']->format('H:i:s') === '00:00:00') ? '24:00:00' : $details['selesai']->format('H:i:s');

        $existingJadwal = Jadwal::where('lapangan_id', $details['lapangan']->id)
            ->where('pelanggan_id', $pelanggan?->id)
            ->whereDate('tanggal', $data['tanggal'])
            ->where('jam_mulai', $details['mulai']->format('H:i:s'))
            ->where(function ($q) use ($selesaiFormatted) {
                $q->where('jam_selesai', $selesaiFormatted)
                  ->orWhere('jam_selesai', '00:00:00');
            })
            ->where('status', 'pending')
            ->first();

        // Kunci baris slot sementara untuk pengguna ini jika belum ada jadwal pending aktif
        if (! $existingJadwal && $pelanggan && $pelanggan->role !== 'admin') {
            $existingJadwal = Jadwal::create([
                'lapangan_id' => $details['lapangan']->id,
                'pelanggan_id' => $pelanggan->id,
                'tanggal' => $data['tanggal'],
                'jam_mulai' => $details['mulai']->format('H:i:s'),
                'jam_selesai' => $selesaiFormatted,
                'harga_disepakati' => $totalHarga,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'belum_bayar',
                'status' => 'pending',
                'created_at' => now(),
            ]);

            BatalBookingOtomatis::dispatch($existingJadwal->id)->delay(now()->addMinutes(10));
        }

        // Hitung sisa waktu pembayaran dalam detik (10 menit batas timeout)
        $sisaDetik = 600;
        if ($existingJadwal) {
            $createdAt = Carbon::parse($existingJadwal->created_at);
            $elapsedSeconds = (int) now()->diffInSeconds($createdAt, false);
            $sisaDetik = max(0, 600 - abs($elapsedSeconds));
        }

        return view('checkout.index', [
            'lapangan' => $details['lapangan'],
            'tanggal' => $data['tanggal'],
            'jamMulai' => $details['mulai']->format('H:i'),
            'jamSelesai' => $details['selesai']->format('H:i'),
            'durasi' => $details['durasi'],
            'hargaPerJam' => $details['hargaPerJam'],
            'totalHarga' => $totalHarga,
            'dpPersen' => 50,
            'nominalDp' => $nominalDp,
            'tipeBayar' => $tipeBayar,
            'nominalBayar' => $nominalBayar,
            'sisaPembayaran' => $sisaPembayaran,
            'persenBayar' => $persenBayar,
            'pelanggan' => $pelanggan,
            'bookingId' => $existingJadwal?->id,
            'jadwalId' => $existingJadwal?->id,
            'sisaDetik' => $sisaDetik,
        ]);
    }

    /**
     * Polling status pembayaran transaksi untuk auto-redirect.
     */
    public function status(string $orderId): JsonResponse
    {
        return $this->checkStatus($orderId);
    }

    public function checkStatus(string $orderId): JsonResponse
    {
        $query = Transaksi::where('order_id', $orderId)
            ->orWhere('referensi_midtrans', $orderId);

        if (Str::isUuid($orderId)) {
            $query->orWhere('id', $orderId);
        }

        $transaksi = $query->first();

        if (! $transaksi) {
            return response()->json([
                'status' => 'not_found',
                'is_paid' => false,
                'redirect_url' => route('home'),
            ], 404);
        }

        $isPaid = in_array($transaksi->status, ['berhasil', 'lunas'], true);

        return response()->json([
            'status' => $transaksi->status,
            'is_paid' => $isPaid,
            'redirect_url' => route('checkout.success', ['orderId' => $transaksi->order_id ?: $orderId]),
        ]);
    }

    /**
     * Halaman Sukses Publik & E-Tiket Masuk (dapat diakses tamu tanpa login).
     */
    public function success(string $orderId, PaymentService $paymentService): View|\Illuminate\Http\RedirectResponse
    {
        $query = Transaksi::with(['jadwal.lapangan', 'jadwal.pelanggan'])
            ->where('order_id', $orderId)
            ->orWhere('referensi_midtrans', $orderId);

        if (Str::isUuid($orderId)) {
            $query->orWhere('id', $orderId);
        }

        $transaksi = $query->firstOrFail();

        // Sinkronisasi status pembayaran dengan Midtrans jika masih pending
        if ($transaksi->status === 'pending') {
            $paymentService->syncStatus($transaksi);
            $transaksi->refresh();
        }

        // Jika transaksi masih pending atau belum berhasil dibayar, jangan terbitkan e-tiket!
        if ($transaksi->status === 'pending' || $transaksi->status === 'menunggu_pembayaran') {
            return redirect()->route('pelanggan.riwayat')
                ->with('info', 'Pembayaran untuk pesanan ' . $transaksi->order_id . ' masih berstatus pending. Silakan selesaikan pembayaran sesuai instruksi.');
        }

        if ($transaksi->status === 'gagal' || $transaksi->status === 'dibatalkan') {
            return redirect()->route('pelanggan.riwayat')
                ->with('error', 'Pembayaran untuk pesanan ' . $transaksi->order_id . ' telah kedaluwarsa atau dibatalkan.');
        }

        $jadwal = $transaksi->jadwal;

        // Pastikan kode_qr dan token_manual terisi
        if ($jadwal) {
            $dirty = false;
            if (empty($jadwal->kode_qr)) {
                $jadwal->kode_qr = 'LPNG-QR-'.strtoupper(Str::random(12)).'-'.time();
                $dirty = true;
            }
            if (empty($jadwal->token_manual) || !preg_match('/^[A-Z0-9]{6}$/', $jadwal->token_manual)) {
                do {
                    $token = strtoupper(Str::random(6));
                } while (Jadwal::where('token_manual', $token)->where('id', '!=', $jadwal->id)->exists());

                $jadwal->token_manual = $token;
                $dirty = true;
            }
            if ($dirty) {
                $jadwal->save();
            }
        }

        // Tentukan nama & nomor WhatsApp pemesan
        $namaPemesan = $jadwal?->nama_tamu ?: ($jadwal?->pelanggan?->nama ?? 'Tamu Lapangan');
        $noHpPemesan = $jadwal?->no_wa_tamu ?: ($jadwal?->pelanggan?->no_hp ?? '');

        $totalHarga = (float) ($jadwal?->harga_disepakati ?? 0);
        $nominalTerbayar = (float) $transaksi->jumlah;
        $isFull = ($transaksi->jenis === 'full' || $nominalTerbayar >= $totalHarga);
        $sisaBayar = max(0, $totalHarga - $nominalTerbayar);

        return view('checkout.success', compact(
            'transaksi',
            'jadwal',
            'namaPemesan',
            'noHpPemesan',
            'totalHarga',
            'nominalTerbayar',
            'isFull',
            'sisaBayar'
        ));
    }

    /**
     * Proses pembuatan pesanan dan generate Snap Token Midtrans.
     */
    public function pay(Request $request, PaymentService $paymentService): JsonResponse
    {
        Jadwal::autoCancelExpiredPendingBookings();

        // 1. Pemesanan online hanya dapat dilakukan oleh pengguna yang telah login / memiliki akun
        if (! Auth::check()) {
            return response()->json([
                'status' => 'unauthenticated',
                'message' => 'Pemesanan online hanya dapat dilakukan oleh pelanggan yang memiliki akun. Silakan daftar atau masuk terlebih dahulu.',
                'redirect_url' => route('register'),
            ], 401);
        }

        $user = Auth::user();

        // 2. Validasi slot waktu: jika hari ini, jam mulai tidak boleh sudah lewat dari jam sekarang
        if ($request->filled('tanggal') && $request->filled('jam_mulai')) {
            $now = Carbon::now();
            $reqTanggal = $request->input('tanggal');
            $reqJamMulai = substr((string) $request->input('jam_mulai'), 0, 5);
            $isToday = $reqTanggal === $now->toDateString();
            $currentTime = $now->format('H:i');

            if ($reqTanggal < $now->toDateString() || ($isToday && $reqJamMulai <= $currentTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jam sewa yang dipilih sudah lewat dari waktu saat ini.',
                ], 422);
            }
        }

        $tipeBayar = $request->input('tipe_bayar', 'dp');
        if (! in_array($tipeBayar, ['dp', 'full'], true)) {
            $tipeBayar = 'dp';
        }

        // Metode pembayaran spesifik (opsional dari UI direct payment)
        $metodeBayar = $request->input('metode_bayar');
        $channelPayments = match ($metodeBayar) {
            'qris' => ['gopay', 'shopeepay'],
            'gopay' => ['gopay'],
            'shopeepay' => ['shopeepay'],
            'bca_va' => ['bca_va'],
            'bni_va' => ['bni_va'],
            'bri_va' => ['bri_va'],
            'mandiri_va' => ['echannel'],
            'permata_va' => ['permata_va'],
            'other_va' => ['other_va'],
            'credit_card' => ['credit_card'],
            default => []
        };

        // Jika request membawa jadwal_id atau booking_id
        if ($request->filled('jadwal_id') || $request->filled('booking_id')) {
            $jadwalId = $request->input('jadwal_id') ?: $request->input('booking_id');
            $jadwal = Jadwal::with('lapangan')->find($jadwalId);

            if ($jadwal && ($jadwal->pelanggan_id === $user->id || empty($jadwal->pelanggan_id))) {
                if (empty($jadwal->pelanggan_id)) {
                    $jadwal->update([
                        'pelanggan_id' => $user->id,
                        'nama_tamu' => $user->nama,
                        'no_wa_tamu' => $user->no_hp,
                    ]);
                }

                // Periksa apakah batas waktu 10 menit telah kedaluwarsa
                $createdAt = Carbon::parse($jadwal->created_at);
                if ($jadwal->status === 'dibatalkan' || $createdAt->lte(now()->subMinutes(10))) {
                    $jadwal->update(['status' => 'dibatalkan']);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Batas waktu pembayaran 10 menit telah kedaluwarsa. Jadwal telah dibatalkan otomatis.',
                    ], 422);
                }

                $totalHarga = (float) $jadwal->harga_disepakati;
                $nominalBayar = ($tipeBayar === 'full') ? $totalHarga : round($totalHarga * 0.5);

                $transaksi = Transaksi::where('jadwal_id', $jadwal->id)
                    ->where('jenis', $tipeBayar)
                    ->where('status', 'pending')
                    ->first();

                if (! $transaksi) {
                    $orderId = 'LPNG-'.date('Ymd').'-'.strtoupper(Str::random(6));
                    $transaksi = Transaksi::create([
                        'jadwal_id' => $jadwal->id,
                        'jenis' => $tipeBayar,
                        'jumlah' => $nominalBayar,
                        'metode' => 'online',
                        'status' => 'pending',
                        'order_id' => $orderId,
                        'referensi_midtrans' => $orderId,
                    ]);
                } elseif ((float) $transaksi->jumlah !== (float) $nominalBayar) {
                    $transaksi->update([
                        'jumlah' => $nominalBayar,
                        'snap_token' => null,
                    ]);
                }

                $lapanganNama = $jadwal->lapangan?->nama ?? 'Lapangan';
                $label = ($tipeBayar === 'full' ? 'Pelunasan Sewa ' : 'DP Sewa ');
                $itemDetails = [
                    [
                        'id' => (string) ($jadwal->lapangan_id ?? $transaksi->order_id),
                        'price' => (int) $nominalBayar,
                        'quantity' => 1,
                        'name' => mb_substr($label.$lapanganNama, 0, 50),
                    ],
                ];

                $snapToken = $paymentService->createSnapToken($transaksi, $user, $itemDetails, $channelPayments);

                return response()->json([
                    'status' => 'success',
                    'snap_token' => $snapToken,
                    'order_id' => $transaksi->order_id,
                ]);
            }
        }

        $data = $request->validate([
            'lapangan_id' => 'required|uuid|exists:lapangans,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'durasi' => 'required|integer|min:1|max:4',
            'tipe_bayar' => 'nullable|string|in:dp,full',
        ]);

        $tipeBayar = $data['tipe_bayar'] ?? $tipeBayar;

        $details = $this->calculateBooking(
            $data['lapangan_id'],
            $data['tanggal'],
            $data['jam_mulai'],
            (int) $data['durasi'],
            $user?->id
        );

        $totalHarga = $details['totalHarga'];
        $nominalBayar = ($tipeBayar === 'full') ? (float) $totalHarga : round($totalHarga * 0.5);

        $selesaiFormatted = ($details['selesai']->format('H:i:s') === '00:00:00') ? '24:00:00' : $details['selesai']->format('H:i:s');

        // Ambil atau buat booking jadwal yang belum dibayar oleh pengguna ini
        $jadwal = Jadwal::where('lapangan_id', $details['lapangan']->id)
            ->where('pelanggan_id', $user->id)
            ->whereDate('tanggal', $data['tanggal'])
            ->where('jam_mulai', $details['mulai']->format('H:i:s'))
            ->where(function ($q) use ($selesaiFormatted) {
                $q->where('jam_selesai', $selesaiFormatted)
                  ->orWhere('jam_selesai', '00:00:00');
            })
            ->where('status', 'pending')
            ->first();

        if (! $jadwal) {
            // Batalkan sesi pending belum bayar dari pengguna lain pada slot yang sama jika ada
            $unpaidOther = Jadwal::where('lapangan_id', $details['lapangan']->id)
                ->whereDate('tanggal', $data['tanggal'])
                ->where('jam_mulai', $details['mulai']->format('H:i:s'))
                ->where(function ($q) use ($selesaiFormatted) {
                    $q->where('jam_selesai', $selesaiFormatted)
                      ->orWhere('jam_selesai', '00:00:00');
                })
                ->where('status', 'pending')
                ->where('status_pembayaran', 'belum_bayar')
                ->first();

            if ($unpaidOther) {
                $unpaidOther->update(['status' => 'dibatalkan']);
                Transaksi::where('jadwal_id', $unpaidOther->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'gagal']);
            }

            $jadwal = Jadwal::create([
                'lapangan_id' => $details['lapangan']->id,
                'pelanggan_id' => $user->id,
                'nama_tamu' => $user->nama,
                'no_wa_tamu' => $user->no_hp,
                'tanggal' => $data['tanggal'],
                'jam_mulai' => $details['mulai']->format('H:i:s'),
                'jam_selesai' => $selesaiFormatted,
                'harga_disepakati' => $totalHarga,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'belum_bayar',
                'status' => 'pending',
                'created_at' => now(),
            ]);

            BatalBookingOtomatis::dispatch($jadwal->id)->delay(now()->addMinutes(10));
        }

        // Ambil atau buat transaksi sesuai tipe bayar
        $transaksi = Transaksi::where('jadwal_id', $jadwal->id)
            ->where('jenis', $tipeBayar)
            ->where('status', 'pending')
            ->first();

        if (! $transaksi) {
            $orderId = 'LPNG-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $transaksi = Transaksi::create([
                'jadwal_id' => $jadwal->id,
                'jenis' => $tipeBayar,
                'jumlah' => $nominalBayar,
                'metode' => 'online',
                'status' => 'pending',
                'order_id' => $orderId,
                'referensi_midtrans' => $orderId,
            ]);
        } elseif ((float) $transaksi->jumlah !== (float) $nominalBayar) {
            $transaksi->update([
                'jumlah' => $nominalBayar,
                'snap_token' => null,
            ]);
        }

        $label = ($tipeBayar === 'full' ? 'Pelunasan Sewa ' : 'DP Sewa ');
        $itemDetails = [
            [
                'id' => (string) $details['lapangan']->id,
                'price' => (int) $nominalBayar,
                'quantity' => 1,
                'name' => mb_substr($label.$details['lapangan']->nama.' ('.$data['durasi'].' Jam)', 0, 50),
            ],
        ];

        $snapToken = $paymentService->createSnapToken($transaksi, $user, $itemDetails, $channelPayments);

        return response()->json([
            'status' => 'success',
            'snap_token' => $snapToken,
            'order_id' => $transaksi->order_id,
        ]);
    }

    /**
     * Batalkan slot reservasi secara otomatis saat batas waktu 10 menit habis.
     */
    public function expire(string $id): JsonResponse
    {
        $jadwal = Jadwal::find($id);

        if ($jadwal && $jadwal->status === 'pending' && $jadwal->status_pembayaran === 'belum_bayar') {
            $jadwal->update([
                'status' => 'dibatalkan',
            ]);

            Transaksi::where('jadwal_id', $jadwal->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'gagal',
                ]);
        }

        return response()->json([
            'status' => 'cancelled',
            'message' => 'Slot reservasi telah dibatalkan otomatis karena batas waktu 10 menit telah habis.',
        ]);
    }

    /**
     * Hitung harga dan validasi ketersediaan interval slot booking.
     */
    protected function calculateBooking(
        string $lapanganId,
        string $tanggal,
        string $jamMulai,
        int $durasi,
        ?string $currentUserId = null
    ): array {
        Jadwal::autoCancelExpiredPendingBookings();

        $lapangan = Lapangan::with('hargaMasters')->findOrFail($lapanganId);

        $hargaKhususAktif = HargaKhusus::query()
            ->where('lapangan_id', $lapangan->id)
            ->whereDate('tanggal', $tanggal)
            ->where('is_active', true)
            ->orderBy('jam_mulai')
            ->get();

        $baseDate = Carbon::parse($tanggal)->startOfDay();
        $jamBuka = $baseDate->copy()->setTimeFromTimeString(substr($lapangan->jam_buka, 0, 5));
        $jamTutupStr = substr($lapangan->jam_tutup, 0, 5);
        $jamTutup = ($jamTutupStr === '24:00' || $jamTutupStr === '00:00')
            ? $baseDate->copy()->addDay()->startOfDay()
            : $baseDate->copy()->setTimeFromTimeString($jamTutupStr);

        $mulai = $baseDate->copy()->setTimeFromTimeString($jamMulai);
        $selesai = $mulai->copy()->addHours($durasi);

        // Validasi interval berada dalam jam operasional
        if ($mulai->lt($jamBuka) || $selesai->gt($jamTutup)) {
            abort(422, 'Interval jadwal yang dipilih berada di luar jam operasional lapangan.');
        }

        // Validasi slot tidak tumpang tindih dengan jadwal aktif lain
        $isOccupied = Jadwal::where('lapangan_id', $lapangan->id)
            ->whereDate('tanggal', $tanggal)
            ->whereNotIn('status', ['dibatalkan', 'cancelled', 'rejected'])
            ->get()
            ->contains(function ($jadwal) use ($baseDate, $mulai, $selesai, $currentUserId) {
                // Jadwal pending yang belum dibayar diperiksa batas waktu 10 menitnya
                if ($jadwal->status === 'pending' && $jadwal->status_pembayaran === 'belum_bayar') {
                    $jadwalCreatedAt = Carbon::parse($jadwal->created_at);
                    if ($jadwalCreatedAt->lte(now()->subMinutes(10))) {
                        $jadwal->update(['status' => 'dibatalkan']);
                        return false;
                    }
                }

                // Jangan bentrok dengan jadwal pending milik user yang sama yang sedang diproses
                if ($currentUserId && $jadwal->pelanggan_id === $currentUserId && $jadwal->status === 'pending') {
                    $jMulaiCheck = $baseDate->copy()->setTimeFromTimeString(substr($jadwal->jam_mulai, 0, 5));
                    $jSelesaiStrCheck = substr($jadwal->jam_selesai, 0, 5);
                    $jSelesaiCheck = ($jSelesaiStrCheck === '24:00' || $jSelesaiStrCheck === '00:00')
                        ? $baseDate->copy()->addDay()->startOfDay()
                        : $baseDate->copy()->setTimeFromTimeString($jSelesaiStrCheck);

                    if ($mulai->eq($jMulaiCheck) && $selesai->eq($jSelesaiCheck)) {
                        return false;
                    }
                }

                $jMulai = $baseDate->copy()->setTimeFromTimeString(substr($jadwal->jam_mulai, 0, 5));
                $jSelesaiStr = substr($jadwal->jam_selesai, 0, 5);
                $jSelesai = ($jSelesaiStr === '24:00' || $jSelesaiStr === '00:00')
                    ? $baseDate->copy()->addDay()->startOfDay()
                    : $baseDate->copy()->setTimeFromTimeString($jSelesaiStr);

                return $mulai->lt($jSelesai) && $selesai->gt($jMulai);
            });

        if ($isOccupied) {
            abort(422, 'Slot jadwal yang dipilih telah terisi atau tidak tersedia.');
        }

        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [0, 6], true);
        $jenisHari = $isWeekend ? 'weekend' : 'weekday';

        $totalHarga = 0;
        $hourlyRates = [];

        for ($i = 0; $i < $durasi; $i++) {
            $slotStart = $mulai->copy()->addHours($i);
            $slotEnd = $slotStart->copy()->addHour();

            // Cek harga khusus tanggal ini yang mencakup jam ini
            $customPrice = $hargaKhususAktif->first(
                function ($hk) use ($baseDate, $slotStart, $slotEnd) {
                    $hkMulai = $baseDate->copy()->setTimeFromTimeString(
                        substr($hk->jam_mulai, 0, 5)
                    );

                    $hkSelesaiStr = substr($hk->jam_selesai, 0, 5);
                    $hkSelesai = in_array($hkSelesaiStr, ['24:00', '00:00'], true)
                        ? $baseDate->copy()->addDay()->startOfDay()
                        : $baseDate->copy()->setTimeFromTimeString($hkSelesaiStr);

                    return $slotStart->gte($hkMulai)
                        && $slotEnd->lte($hkSelesai);
                }
            );

            if ($customPrice) {
                // Prioritas pertama: harga khusus dari tabel harga_khusus.
                $rate = (float) $customPrice->harga;
            } else {
                // Prioritas kedua: harga master weekday/weekend.
                $defaultPrice = $lapangan->hargaMasters
                    ->filter(function ($hm) use ($jenisHari, $baseDate, $slotStart, $slotEnd) {
                        if (
                            $hm->tanggal_khusus !== null
                            || $hm->jenis_hari !== $jenisHari
                        ) {
                            return false;
                        }

                        if (! $hm->jam_mulai || ! $hm->jam_selesai) {
                            return true;
                        }

                        $hmMulai = $baseDate->copy()->setTimeFromTimeString(
                            substr($hm->jam_mulai, 0, 5)
                        );

                        $hmSelesaiStr = substr($hm->jam_selesai, 0, 5);
                        $hmSelesai = in_array($hmSelesaiStr, ['24:00', '00:00'], true)
                            ? $baseDate->copy()->addDay()->startOfDay()
                            : $baseDate->copy()->setTimeFromTimeString($hmSelesaiStr);

                        return $slotStart->gte($hmMulai)
                            && $slotEnd->lte($hmSelesai);
                    })
                    ->first();

                if (! $defaultPrice) {
                    $defaultPrice = $lapangan->hargaMasters
                        ->whereNull('tanggal_khusus')
                        ->where('jenis_hari', $jenisHari)
                        ->first();
                }

                $rate = (float) ($defaultPrice?->harga ?? 150000);
            }

            $hourlyRates[] = $rate;
            $totalHarga += $rate;
        }

        $hargaPerJam = $durasi > 0 ? ($totalHarga / $durasi) : 0;
        $dpPersen = 50;
        $nominalDp = round($totalHarga * $dpPersen / 100);
        $sisaPembayaran = $totalHarga - $nominalDp;

        return [
            'lapangan' => $lapangan,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'durasi' => $durasi,
            'totalHarga' => $totalHarga,
            'hargaPerJam' => $hargaPerJam,
            'dpPersen' => $dpPersen,
            'nominalDp' => $nominalDp,
            'sisaPembayaran' => $sisaPembayaran,
            'hourlyRates' => $hourlyRates,
        ];
    }
}
