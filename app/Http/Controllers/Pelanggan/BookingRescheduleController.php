<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Services\BookingAdjustmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingRescheduleController extends Controller
{
    public function __construct(
        protected BookingAdjustmentService $adjustmentService
    ) {}

    /**
     * Tampilkan formulir pengajuan reschedule jadwal sewa lapangan.
     */
    public function create(Request $request, string $id): View|RedirectResponse
    {
        $pelanggan = $request->user();

        $jadwal = Jadwal::with(['lapangan', 'transaksis', 'reschedules'])
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        $eligibility = $this->adjustmentService->checkRescheduleEligibility($jadwal);

        if (! $eligibility['eligible']) {
            return redirect()->route('pelanggan.riwayat')
                ->with('error', $eligibility['reason'] ?? 'Jadwal tidak memenuhi syarat untuk reschedule.');
        }

        // Hitung durasi jam sewa asli
        $mulaiCarbon = Carbon::parse($jadwal->jam_mulai);
        $selesaiCarbon = Carbon::parse(
            in_array(substr((string) $jadwal->jam_selesai, 0, 5), ['00:00', '24:00'], true)
                ? '23:59:59'
                : $jadwal->jam_selesai
        );
        $durasiJam = max(1, (int) round($mulaiCarbon->diffInMinutes($selesaiCarbon) / 60));

        // Rentang tanggal yang tersedia untuk reschedule (besok sampai 14 hari ke depan)
        $minDate = now()->addDay()->toDateString();
        $maxDate = now()->addDays(14)->toDateString();

        return view('Pelanggan.booking.reschedule', [
            'jadwal' => $jadwal,
            'lapangan' => $jadwal->lapangan,
            'durasiJam' => $durasiJam,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            'eligibility' => $eligibility,
        ]);
    }

    /**
     * Endpoint API untuk mengecek ketersediaan slot jam berdasarkan tanggal baru.
     */
    public function checkSlots(Request $request, string $id): JsonResponse
    {
        $pelanggan = $request->user();

        $jadwal = Jadwal::with('lapangan')
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        $request->validate([
            'tanggal' => 'required|date|after_or_equal:tomorrow',
        ]);

        $tanggal = $request->input('tanggal');
        $lapangan = $jadwal->lapangan;

        $mulaiCarbon = Carbon::parse($jadwal->jam_mulai);
        $selesaiCarbon = Carbon::parse(
            in_array(substr((string) $jadwal->jam_selesai, 0, 5), ['00:00', '24:00'], true)
                ? '23:59:59'
                : $jadwal->jam_selesai
        );
        $durasiJam = max(1, (int) round($mulaiCarbon->diffInMinutes($selesaiCarbon) / 60));

        $jamBuka = Carbon::parse($lapangan->jam_buka ?? '08:00');
        $jamTutup = Carbon::parse($lapangan->jam_tutup ?? '23:00');

        $availableSlots = [];

        for ($curr = $jamBuka->copy(); $curr->copy()->addHours($durasiJam)->lte($jamTutup); $curr->addHour()) {
            $slotMulai = $curr->format('H:i');
            $slotSelesai = $curr->copy()->addHours($durasiJam)->format('H:i');

            // Cek apakah slot berjarak minimal 24 jam dari waktu saat ini
            $slotDateTime = Carbon::parse($tanggal . ' ' . $slotMulai);
            $hoursUntilSlot = now()->diffInHours($slotDateTime, false);
            $isTooClose = $hoursUntilSlot < BookingAdjustmentService::H_MINUS_HOURS;

            $isAvailable = ! $isTooClose && $this->adjustmentService->checkSlotAvailability(
                $lapangan,
                $tanggal,
                $slotMulai,
                $slotSelesai,
                $jadwal->id
            );

            $availableSlots[] = [
                'jam_mulai' => $slotMulai,
                'jam_selesai' => $slotSelesai,
                'label' => "{$slotMulai} - {$slotSelesai} WIB ({$durasiJam} Jam)",
                'is_available' => $isAvailable,
                'is_too_close' => $isTooClose,
                'hours_until_slot' => (int) $hoursUntilSlot,
            ];
        }

        return response()->json([
            'tanggal' => $tanggal,
            'durasi_jam' => $durasiJam,
            'slots' => $availableSlots,
        ]);
    }

    /**
     * Simpan pengajuan reschedule dari formulir pelanggan.
     */
    public function store(Request $request, string $id): RedirectResponse
    {
        $pelanggan = $request->user();

        $jadwal = Jadwal::with('lapangan')
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        $validated = $request->validate([
            'tanggal_baru' => 'required|date|after_or_equal:tomorrow',
            'jam_mulai_baru' => 'required|date_format:H:i',
            'jam_selesai_baru' => 'required|date_format:H:i|after:jam_mulai_baru',
            'alasan_pengajuan' => 'required|string|min:5|max:1000',
        ], [
            'tanggal_baru.after_or_equal' => 'Tanggal jadwal baru minimal H+1 dari hari ini.',
            'jam_mulai_baru.required' => 'Jam mulai harus dipilih.',
            'jam_selesai_baru.required' => 'Jam selesai harus dipilih.',
            'jam_selesai_baru.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'alasan_pengajuan.required' => 'Mohon sertakan alasan pengajuan perubahan jadwal.',
            'alasan_pengajuan.min' => 'Alasan pengajuan minimal 5 karakter.',
        ]);

        $reschedule = $this->adjustmentService->requestReschedule(
            $jadwal,
            $validated['tanggal_baru'],
            $validated['jam_mulai_baru'],
            $validated['jam_selesai_baru'],
            $validated['alasan_pengajuan']
        );

        $tanggalBaruFormatted = Carbon::parse($reschedule->tanggal_baru)->translatedFormat('l, d F Y');

        return redirect()->route('pelanggan.riwayat')
            ->with('success', "Permohonan reschedule untuk {$jadwal->lapangan->nama} pada {$tanggalBaruFormatted} ({$reschedule->jam_mulai_baru} - {$reschedule->jam_selesai_baru}) berhasil diajukan dan sedang menunggu konfirmasi kasir arena.");
    }

    /**
     * Endpoint API untuk melihat simulasi kalkulasi denda penalti pembatalan.
     */
    public function cancelPreview(Request $request, string $id): JsonResponse
    {
        $pelanggan = $request->user();

        $jadwal = Jadwal::with(['lapangan', 'transaksis'])
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        $penaltyData = $this->adjustmentService->calculateCancellationPenalty($jadwal);

        return response()->json([
            'jadwal_id' => $jadwal->id,
            'lapangan_nama' => $jadwal->lapangan?->nama,
            'tanggal' => Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y'),
            'jam' => substr((string) $jadwal->jam_mulai, 0, 5) . ' - ' . substr((string) $jadwal->jam_selesai, 0, 5) . ' WIB',
            'penalty' => $penaltyData,
        ]);
    }

    /**
     * Eksekusi pembatalan sewa mandiri pelanggan dengan auto-penalty policy.
     */
    public function cancel(Request $request, string $id): RedirectResponse
    {
        $pelanggan = $request->user();

        $jadwal = Jadwal::with(['lapangan', 'transaksis'])
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        $validated = $request->validate([
            'alasan_pembatalan' => 'nullable|string|max:500',
        ]);

        $result = $this->adjustmentService->cancelBooking(
            $jadwal,
            $validated['alasan_pembatalan'] ?? null,
            $pelanggan
        );

        $penalty = $result['penalty'];
        $dendaFormatted = 'Rp ' . number_format($penalty['penalty_amount'], 0, ',', '.');
        $refundFormatted = 'Rp ' . number_format($penalty['estimated_refund'], 0, ',', '.');

        $message = "Pemesanan berhasil dibatalkan dan slot jadwal telah dilepaskan kembali ke publik. Denda Penalti: {$dendaFormatted} ({$penalty['policy_badge']}).";
        if ($penalty['estimated_refund'] > 0) {
            $message .= " Estimasi pengembalian dana: {$refundFormatted}.";
        }

        return redirect()->route('pelanggan.riwayat')->with('warning', $message);
    }
}
