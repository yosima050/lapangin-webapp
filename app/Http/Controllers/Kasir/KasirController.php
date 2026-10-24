<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function index(Request $request): View
    {
        $lapangans = Lapangan::all();
        $code = $request->input('code');

        $activeBooking = null;

        if (! empty($code)) {
            $activeBooking = Jadwal::with([
                'lapangan',
                'pelanggan',
                'transaksis',
            ])
                ->where(function ($query) use ($code) {
                    $query->where('kode_qr', $code)
                        ->orWhere('token_manual', strtoupper($code))
                        ->orWhere('token_manual', $code);

                    if (Str::isUuid($code)) {
                        $query->orWhere('id', $code);
                    }
                })
                ->first();
        }

        if (! $activeBooking) {
            $activeBooking = Jadwal::with([
                'lapangan',
                'pelanggan',
                'transaksis',
            ])
                ->whereDate('tanggal', now()->toDateString())
                ->where('status_pembayaran', '!=', 'lunas_kasir')
                ->orderBy('jam_mulai')
                ->first();

            if (! $activeBooking) {
                $activeBooking = Jadwal::with([
                    'lapangan',
                    'pelanggan',
                    'transaksis',
                ])
                    ->latest('created_at')
                    ->first();
            }
        }

        $totalHarga = $activeBooking
            ? (float) $activeBooking->harga_disepakati
            : 0;

        $dpPaid = $activeBooking
            ? (float) $activeBooking->transaksis
                ->where('status', 'berhasil')
                ->sum('jumlah')
            : 0;

        $sisaTagihan = max(0, $totalHarga - $dpPaid);

        $antreanHariIni = Jadwal::with([
            'lapangan',
            'pelanggan',
            'transaksis',
        ])
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('jam_mulai')
            ->get();

        if ($antreanHariIni->isEmpty()) {
            $antreanHariIni = Jadwal::with([
                'lapangan',
                'pelanggan',
                'transaksis',
            ])
                ->orderBy('jam_mulai')
                ->take(6)
                ->get();
        }

        $totalCapacitySlots = max(12, $antreanHariIni->count() + 4);
        $occupiedSlots = $antreanHariIni->count();

        $occupancyRate = round(
            ($occupiedSlots / $totalCapacitySlots) * 100
        );

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

    public function scan(Request $request): View
    {
        $lapangans = Lapangan::all();

        $recentCheckIns = Jadwal::with([
            'lapangan',
            'pelanggan',
        ])
            ->whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', now()->toDateString())
            ->orderByDesc('checked_in_at')
            ->take(6)
            ->get();

        if (view()->exists('kasir.scan')) {
            return view('kasir.scan', compact(
                'lapangans',
                'recentCheckIns'
            ));
        }

        return view('Kasir.scan', compact(
            'lapangans',
            'recentCheckIns'
        ));
    }

    public function lookupTicket(
        Request $request,
        ?string $code = null
    ): JsonResponse {
        $searchCode = trim(
            (string) ($code ?? $request->input('code', ''))
        );

        if ($searchCode === '') {
            return response()->json([
                'success' => false,
                'message' => 'Kode QR atau token manual wajib diisi.',
            ], 400);
        }

        $jadwal = Jadwal::with([
            'lapangan',
            'pelanggan',
            'transaksis',
        ])
            ->where(function ($query) use ($searchCode) {
                $query->where('kode_qr', $searchCode)
                    ->orWhere('token_manual', strtoupper($searchCode))
                    ->orWhere('token_manual', $searchCode);

                if (Str::isUuid($searchCode)) {
                    $query->orWhere('id', $searchCode);
                }
            })
            ->first();

        if (! $jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan. Periksa kode QR atau token manual.',
            ], 404);
        }

        // Tiket dianggap duplikat jika sudah check-in
        // atau status jadwal sudah selesai.
        // Status lunas_kasir saja tidak berarti sudah check-in.
        $isDuplicate = $jadwal->checked_in_at !== null
            || $jadwal->status === 'selesai';

        $today = now()->toDateString();

        $isExpired = $jadwal->tanggal->toDateString() < $today
            || $jadwal->status === 'dibatalkan';

        $allowedPaymentStatuses = [
            'dp_terverifikasi',
            'lunas_online',
            'belum_bayar',
            'lunas_kasir',
        ];

        $isPaymentStatusValid = in_array(
            $jadwal->status_pembayaran,
            $allowedPaymentStatuses,
            true
        );

        $isValid = ! $isDuplicate
            && ! $isExpired
            && $isPaymentStatusValid;

        $totalHarga = (float) $jadwal->harga_disepakati;

        $dpPaid = (float) $jadwal->transaksis
            ->where('status', 'berhasil')
            ->sum('jumlah');

        $sisaTagihan = max(0, $totalHarga - $dpPaid);

        if ($isDuplicate) {
            $statusLabel = 'Tiket Sudah Digunakan (Duplikat)';
        } elseif ($isExpired) {
            $statusLabel = 'Tiket Kedaluwarsa';
        } elseif (! $isPaymentStatusValid) {
            $statusLabel = 'Status Tiket Tidak Valid';
        } else {
            $statusLabel = 'Tiket Valid';
        }

        return response()->json([
            'success' => true,
            'is_valid' => $isValid,
            'is_duplicate' => $isDuplicate,
            'is_expired' => $isExpired,
            'status_label' => $statusLabel,
            'jadwal' => [
                'id' => $jadwal->id,
                'kode_qr' => $jadwal->kode_qr,
                'token_manual' => $jadwal->token_manual,
                'nama_penyewa' => $jadwal->pelanggan->nama
                    ?? $jadwal->nama_tamu
                    ?? 'Penyewa',
                'no_hp' => $jadwal->pelanggan->no_hp
                    ?? $jadwal->no_wa_tamu
                    ?? '-',
                'nama_lapangan' => $jadwal->lapangan->nama ?? '-',
                'kategori' => $jadwal->lapangan->kategori ?? '-',
                'tanggal' => $jadwal->tanggal->format('d M Y'),
                'tanggal_raw' => $jadwal->tanggal->toDateString(),
                'jam_mulai' => substr($jadwal->jam_mulai, 0, 5),
                'jam_selesai' => substr($jadwal->jam_selesai, 0, 5),
                'total_harga' => $totalHarga,
                'dp_paid' => $dpPaid,
                'sisa_tagihan' => $sisaTagihan,
                'status_pembayaran' => $jadwal->status_pembayaran,
                'status' => $jadwal->status,
                'catatan_kasir' => $jadwal->catatan_kasir,
                'checked_in_at' => $jadwal->checked_in_at,
            ],
            'checkin_url' => route('kasir.checkin', $jadwal->id),
        ]);
    }

    public function checkin(Request $request, string $id)
    {
        $expectsJson = $request->expectsJson()
            || $request->ajax()
            || $request->wantsJson();

        try {
            $result = DB::transaction(function () use ($request, $id) {
                // Kunci baris jadwal untuk mencegah dua request
                // melakukan check-in pada tiket yang sama.
                $jadwal = Jadwal::query()
                    ->where(function ($query) use ($id) {
                        $query->where('kode_qr', $id)
                            ->orWhere('token_manual', strtoupper($id))
                            ->orWhere('token_manual', $id);

                        if (Str::isUuid($id)) {
                            $query->orWhere('id', $id);
                        }
                    })
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $jadwal->checked_in_at !== null
                    || $jadwal->status === 'selesai'
                ) {
                    return [
                        'error' => 'duplicate',
                        'message' => 'Tiket ini sudah pernah digunakan untuk check-in.',
                    ];
                }

                if (
                    $jadwal->tanggal->toDateString() < now()->toDateString()
                    || $jadwal->status === 'dibatalkan'
                ) {
                    return [
                        'error' => 'expired',
                        'message' => 'Tiket kedaluwarsa atau dibatalkan.',
                    ];
                }

                $allowedPaymentStatuses = [
                    'dp_terverifikasi',
                    'lunas_online',
                    'belum_bayar',
                    'lunas_kasir',
                ];

                if (! in_array(
                    $jadwal->status_pembayaran,
                    $allowedPaymentStatuses,
                    true
                )) {
                    return [
                        'error' => 'invalid_status',
                        'message' => 'Status pembayaran tiket tidak memungkinkan check-in.',
                    ];
                }

                // Kunci transaksi terkait selama perhitungan pembayaran.
                $transaksis = Transaksi::query()
                    ->where('jadwal_id', $jadwal->id)
                    ->lockForUpdate()
                    ->get();

                $totalHarga = (float) $jadwal->harga_disepakati;

                $sudahDibayar = (float) $transaksis
                    ->where('status', 'berhasil')
                    ->sum('jumlah');

                $sisaTagihan = max(0, $totalHarga - $sudahDibayar);

                $metode = $request->input('metode', 'tunai');

                if (! in_array($metode, ['cash', 'tunai', 'online'], true)) {
                    throw ValidationException::withMessages([
                        'metode' => 'Metode pembayaran tidak valid.',
                    ]);
                }

                $jumlahBayar = $request->has('jumlah')
                    ? $request->input('jumlah')
                    : $sisaTagihan;

                if (
                    ! is_numeric($jumlahBayar)
                    || ! is_finite((float) $jumlahBayar)
                ) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Nominal pembayaran harus berupa angka yang valid.',
                    ]);
                }

                $jumlahBayar = (float) $jumlahBayar;

                if (
                    $jumlahBayar < 0
                    || $jumlahBayar > $sisaTagihan
                ) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Nominal pembayaran tidak boleh negatif atau melebihi sisa tagihan.',
                    ]);
                }

                // Jika masih ada tagihan, pelunasan harus menutup
                // seluruh sisa tagihan sebelum check-in berhasil.
                if (
                    $sisaTagihan > 0
                    && $jumlahBayar < $sisaTagihan
                ) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Pembayaran harus melunasi seluruh sisa tagihan sebelum check-in.',
                    ]);
                }

                // Hindari membuat transaksi dengan nominal nol.
                if ($sisaTagihan > 0) {
                    Transaksi::create([
                        'jadwal_id' => $jadwal->id,
                        'jenis' => 'pelunasan',
                        'jumlah' => $jumlahBayar,
                        'metode' => in_array(
                            $metode,
                            ['cash', 'tunai'],
                            true
                        ) ? 'tunai' : 'online',
                        'status' => 'berhasil',
                        'dibayar_pada' => now(),
                    ]);
                }

                // Catat audit check-in hanya ketika check-in berhasil.
                $jadwal->update([
                    'status_pembayaran' => 'lunas_kasir',
                    'status' => 'terverifikasi',
                    'catatan_kasir' => $request->input(
                        'catatan',
                        $jadwal->catatan_kasir
                    ),
                    'kasir_id' => $request->user()->getAuthIdentifier(),
                    'checked_in_at' => now(),
                ]);

                return [
                    'jadwal' => $jadwal->fresh([
                        'lapangan',
                        'pelanggan',
                        'transaksis',
                    ]),
                    'jumlah_bayar' => $jumlahBayar,
                ];
            }, 3);
        } catch (ValidationException $exception) {
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'validation',
                    'message' => collect($exception->errors())
                        ->flatten()
                        ->first(),
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        }

        if (isset($result['error'])) {
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => $result['message'],
                ], 422);
            }

            return redirect()->back()->with(
                'error',
                $result['message']
            );
        }

        $jadwal = $result['jadwal'];
        $jumlahBayar = $result['jumlah_bayar'];

        $namaPenyewa = $jadwal->pelanggan->nama
            ?? $jadwal->nama_tamu
            ?? 'Penyewa';

        if ($jumlahBayar > 0) {
            $successMessage = 'Check-in untuk '
                . $namaPenyewa
                . ' berhasil dicatat. Pembayaran yang dicatat: Rp '
                . number_format($jumlahBayar, 0, ',', '.')
                . '.';
        } else {
            $successMessage = 'Check-in untuk '
                . $namaPenyewa
                . ' berhasil dicatat. Tidak ada sisa tagihan.';
        }

        if ($expectsJson) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'jadwal' => $jadwal,
            ]);
        }

        return redirect()->route('kasir.index', [
            'code' => $jadwal->kode_qr
                ?? $jadwal->token_manual
                ?? $jadwal->id,
        ])->with('success', $successMessage);
    }

    public function jadwal(Request $request): View
    {
        $selectedDate = $request->input(
            'tanggal',
            now()->toDateString()
        );

        $selectedCourtId = $request->input('lapangan_id', 'all');

        $lapangans = Lapangan::with('hargaMasters')->get();

        $jadwalsQuery = Jadwal::with([
            'lapangan',
            'pelanggan',
            'transaksis',
        ])->whereDate('tanggal', $selectedDate);

        if (
            $selectedCourtId !== 'all'
            && ! empty($selectedCourtId)
        ) {
            $jadwalsQuery->where('lapangan_id', $selectedCourtId);
        }

        $jadwals = $jadwalsQuery
            ->orderBy('jam_mulai')
            ->get();

        if ($jadwals->isEmpty()) {
            $jadwals = Jadwal::with([
                'lapangan',
                'pelanggan',
                'transaksis',
            ])
                ->orderBy('tanggal')
                ->orderBy('jam_mulai')
                ->take(6)
                ->get();
        }

        $totalDailySlots = 14;
        $occupancyCount = $jadwals->count();

        $occupancyRate = round(
            ($occupancyCount / $totalDailySlots) * 100
        );

        $bookedHours = $jadwals
            ->map(fn ($jadwal) => substr($jadwal->jam_mulai, 0, 5))
            ->toArray();

        $availableSlots = [];

        $operatingHours = [
            '08:00',
            '10:00',
            '12:00',
            '14:00',
            '16:00',
            '18:00',
            '20:00',
            '22:00',
        ];

        foreach ($operatingHours as $hour) {
            if (! in_array($hour, $bookedHours, true)) {
                $availableSlots[] = $hour;
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

    public function storeWalkin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_tamu' => 'required|string|max:100',
            'no_wa_tamu' => 'required|string|max:20',
            'lapangan_id' => 'required|exists:lapangans,id',
            'jam_mulai' => 'required',
            'durasi' => 'required|integer|min:1|max:4',
            'payType' => 'required|in:full,dp',
            'tanggal' => 'nullable|date',
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();

        $jamMulai = Carbon::parse($validated['jam_mulai'])
            ->format('H:i:00');

        $durasi = (int) $validated['durasi'];

        $selesaiCarbon = Carbon::parse($validated['jam_mulai'])
            ->addHours($durasi);

        $jamSelesai = $selesaiCarbon->format('H:i:00') === '00:00:00'
            ? '24:00:00'
            : $selesaiCarbon->format('H:i:00');

        $lapangan = Lapangan::with('hargaMasters')
            ->findOrFail($validated['lapangan_id']);

        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [0, 6], true);

        $customPrice = $lapangan->hargaMasters
            ->firstWhere('tanggal_khusus', $tanggal);

        if ($customPrice) {
            $ratePerHour = (float) $customPrice->harga;
        } else {
            $defaultPrice = $lapangan->hargaMasters
                ->whereNull('tanggal_khusus')
                ->where(
                    'jenis_hari',
                    $isWeekend ? 'weekend' : 'weekday'
                )
                ->first();

            $ratePerHour = $defaultPrice
                ? (float) $defaultPrice->harga
                : 150000;
        }

        $totalTarif = $ratePerHour * $durasi;

        $jadwal = DB::transaction(function () use (
            $validated,
            $tanggal,
            $jamMulai,
            $jamSelesai,
            $durasi,
            $lapangan,
            $totalTarif
        ) {
            $payFull = $validated['payType'] === 'full';

            $jadwal = Jadwal::create([
                'lapangan_id' => $lapangan->id,
                'nama_tamu' => $validated['nama_tamu'],
                'no_wa_tamu' => $validated['no_wa_tamu'],
                'tanggal' => $tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'harga_disepakati' => $totalTarif,
                'biaya_layanan' => 0,
                'status_pembayaran' => $payFull
                    ? 'lunas_kasir'
                    : 'dp_terverifikasi',
                'status' => 'terverifikasi',
                'kode_qr' => 'LPGN-'
                    . now()->format('Ymd')
                    . '-'
                    . strtoupper(Str::random(8)),
                'token_manual' => strtoupper(Str::random(6)),
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'catatan_kasir' => 'Booking Walk-in Meja Kasir POS',
                'created_at' => now(),
            ]);

            $bayarNominal = $payFull
                ? $totalTarif
                : ($totalTarif * 0.5);

            Transaksi::create([
                'jadwal_id' => $jadwal->id,
                'jenis' => $payFull ? 'full' : 'dp',
                'jumlah' => $bayarNominal,
                'metode' => 'tunai',
                'status' => 'berhasil',
                'dibayar_pada' => now(),
            ]);

            // Booking walk-in belum otomatis check-in.
            // checked_in_at dan kasir_id tetap null sampai check-in dilakukan.

            return $jadwal;
        }, 3);

        return redirect()->route('kasir.jadwal', [
            'tanggal' => $tanggal,
            'lapangan_id' => $lapangan->id,
        ])->with(
            'success',
            "Booking walk-in untuk {$jadwal->nama_tamu} di "
            . "{$lapangan->nama} berhasil disimpan dengan kode "
            . "{$jadwal->kode_qr}!"
        );
    }

    public function reschedule(Request $request): View
    {
        $lapangans = Lapangan::with('hargaMasters')->get();

        $reschedules = Reschedule::with([
            'jadwal.lapangan.hargaMasters',
            'jadwal.pelanggan',
            'jadwal.transaksis',
        ])
            ->latest('created_at')
            ->get();

        $urgentCount = Reschedule::where('status', 'diajukan')->count();
        $approvedCount = Reschedule::where('status', 'disetujui')->count();
        $rejectedCount = Reschedule::where('status', 'ditolak')->count();
        $totalPengajuan = $reschedules->count();

        $selectedId = $request->input('id');

        $selectedReschedule = $selectedId
            ? $reschedules->firstWhere('id', $selectedId)
            : (
                $reschedules->where('status', 'diajukan')->first()
                ?? $reschedules->first()
            );

        $isSlotAvailable = true;
        $tarifSesiBaru = 0;
        $selisihBiaya = 0;

        if ($selectedReschedule) {
            $conflictCount = Jadwal::where(
                'lapangan_id',
                $selectedReschedule->jadwal?->lapangan_id
            )
                ->where('id', '!=', $selectedReschedule->jadwal_id)
                ->whereDate('tanggal', $selectedReschedule->tanggal_baru)
                ->where(function ($query) use ($selectedReschedule) {
                    $query->whereBetween('jam_mulai', [
                        $selectedReschedule->jam_mulai_baru,
                        $selectedReschedule->jam_selesai_baru,
                    ])->orWhereBetween('jam_selesai', [
                        $selectedReschedule->jam_mulai_baru,
                        $selectedReschedule->jam_selesai_baru,
                    ]);
                })
                ->count();

            $isSlotAvailable = $conflictCount === 0;

            $court = $selectedReschedule->jadwal?->lapangan;

            if ($court) {
                $newDate = Carbon::parse(
                    $selectedReschedule->tanggal_baru
                );

                $newDayOfWeek = $newDate->dayOfWeek;
                $isNewWeekend = in_array(
                    $newDayOfWeek,
                    [0, 6],
                    true
                );

                $customPrice = $court->hargaMasters
                    ->firstWhere(
                        'tanggal_khusus',
                        $newDate->toDateString()
                    );

                if ($customPrice) {
                    $ratePerHour = (float) $customPrice->harga;
                } else {
                    $regular = $court->hargaMasters
                        ->whereNull('tanggal_khusus')
                        ->where(
                            'jenis_hari',
                            $isNewWeekend ? 'weekend' : 'weekday'
                        )
                        ->first();

                    $ratePerHour = $regular
                        ? (float) $regular->harga
                        : (float) (
                            $selectedReschedule->jadwal?->harga_disepakati
                            ?? 0
                        );
                }

                $start = Carbon::parse(
                    $selectedReschedule->jam_mulai_baru
                );

                $end = Carbon::parse(
                    $selectedReschedule->jam_selesai_baru
                );

                if ($end->lessThanOrEqualTo($start)) {
                    $end->addDay();
                }

                $durasiJam = max(
                    1,
                    (int) $start->diffInHours($end)
                );

                $tarifSesiBaru = $ratePerHour * $durasiJam;
            } else {
                $tarifSesiBaru = (float) (
                    $selectedReschedule->jadwal?->harga_disepakati
                    ?? 0
                );
            }

            $selisihBiaya = $tarifSesiBaru
                - (float) (
                    $selectedReschedule->jadwal?->harga_disepakati
                    ?? 0
                );
        }

        $bookedTodayCount = Jadwal::whereDate(
            'tanggal',
            now()->toDateString()
        )->count();

        $totalDailySlots = max(
            0,
            ($lapangans->count() * 12) - $bookedTodayCount
        );

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

    public function approveReschedule(
        Request $request,
        string $id
    ): RedirectResponse {
        DB::transaction(function () use ($request, $id) {
            $reschedule = Reschedule::with('jadwal')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($reschedule->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'reschedule' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            if (! $reschedule->jadwal) {
                throw ValidationException::withMessages([
                    'jadwal' => 'Jadwal terkait tidak ditemukan.',
                ]);
            }

            $reschedule->jadwal->update([
                'tanggal' => $reschedule->tanggal_baru,
                'jam_mulai' => $reschedule->jam_mulai_baru,
                'jam_selesai' => $reschedule->jam_selesai_baru,
                'catatan_kasir' => $request->input(
                    'catatan',
                    'Reschedule disetujui kasir pada '
                    . now()->format('d/m/Y H:i')
                ),
            ]);

            $reschedule->update([
                'status' => 'disetujui',
            ]);
        }, 3);

        return redirect()->route('kasir.reschedule')
            ->with(
                'success',
                'Permohonan reschedule berhasil disetujui dan jadwal diperbarui.'
            );
    }

    public function rejectReschedule(
        Request $request,
        string $id
    ): RedirectResponse {
        $reschedule = Reschedule::with('jadwal')->findOrFail($id);

        if ($reschedule->status !== 'diajukan') {
            return redirect()->route('kasir.reschedule')
                ->with('warning', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $reschedule->update([
            'status' => 'ditolak',
        ]);

        $namaPenyewa = $reschedule->jadwal?->pelanggan?->nama
            ?? $reschedule->jadwal?->nama_tamu
            ?? 'Penyewa';

        return redirect()->route('kasir.reschedule')
            ->with(
                'warning',
                "Permohonan reschedule untuk {$namaPenyewa} ditolak. "
                . 'Jadwal reservasi lama tetap dipertahankan.'
            );
    }

    public function transaksi(): View
    {
        $lapangans = Lapangan::all();

        $transaksis = Transaksi::with([
            'jadwal.lapangan',
            'jadwal.pelanggan',
        ])
            ->latest('dibayar_pada')
            ->get();

        $totalTunai = Transaksi::where('metode', 'tunai')
            ->where('status', 'berhasil')
            ->sum('jumlah');

        $totalNonTunai = Transaksi::where('metode', 'online')
            ->where('status', 'berhasil')
            ->sum('jumlah');

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