<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingAdjustmentService
{
    /**
     * Batas jam minimal sebelum kickoff untuk reschedule & penalti pembatalan.
     */
    public const H_MINUS_HOURS = 24;

    /**
     * Mengecek kelayakan jadwal untuk pengajuan reschedule mandiri oleh pelanggan.
     *
     * @return array{
     *     eligible: bool,
     *     reason: string|null,
     *     hours_until_kickoff: float|int,
     *     has_pending_reschedule: bool,
     *     pending_reschedule: Reschedule|null
     * }
     */
    public function checkRescheduleEligibility(Jadwal $jadwal): array
    {
        // 1. Cek apakah jadwal sudah dibatalkan atau selesai
        if (in_array($jadwal->status, ['dibatalkan', 'cancelled', 'rejected', 'selesai'], true)) {
            return [
                'eligible' => false,
                'reason' => 'Jadwal yang sudah ' . ($jadwal->status === 'selesai' ? 'selesai' : 'dibatalkan') . ' tidak dapat diajukan reschedule.',
                'hours_until_kickoff' => 0,
                'has_pending_reschedule' => false,
                'pending_reschedule' => null,
            ];
        }

        // 2. Cek apakah penyewa sudah check-in di venue
        if (!empty($jadwal->checked_in_at)) {
            return [
                'eligible' => false,
                'reason' => 'Tiket sudah digunakan untuk check-in di arena, permohonan reschedule tidak diizinkan.',
                'hours_until_kickoff' => 0,
                'has_pending_reschedule' => false,
                'pending_reschedule' => null,
            ];
        }

        // 3. Cek apakah sudah ada pengajuan reschedule yang masih menunggu review kasir
        $pendingReschedule = $jadwal->reschedules()->where('status', 'diajukan')->first();
        if ($pendingReschedule) {
            return [
                'eligible' => false,
                'reason' => 'Jadwal ini sedang dalam proses review permohonan reschedule oleh kasir venue.',
                'hours_until_kickoff' => 0,
                'has_pending_reschedule' => true,
                'pending_reschedule' => $pendingReschedule,
            ];
        }

        // 4. Cek batas waktu minimal H-1 (24 jam sebelum kickoff)
        $kickoff = $this->getKickoffDateTime($jadwal);
        $hoursUntilKickoff = now()->diffInHours($kickoff, false);

        if ($hoursUntilKickoff < self::H_MINUS_HOURS) {
            return [
                'eligible' => false,
                'reason' => 'Pengajuan reschedule hanya dapat dilakukan maksimal H-1 (minimal ' . self::H_MINUS_HOURS . ' jam sebelum waktu bermain). Sisa waktu saat ini: ' . max(0, (int) $hoursUntilKickoff) . ' jam.',
                'hours_until_kickoff' => $hoursUntilKickoff,
                'has_pending_reschedule' => false,
                'pending_reschedule' => null,
            ];
        }

        return [
            'eligible' => true,
            'reason' => null,
            'hours_until_kickoff' => $hoursUntilKickoff,
            'has_pending_reschedule' => false,
            'pending_reschedule' => null,
        ];
    }

    /**
     * Mengecek apakah slot tujuan tersedia (tidak bentrok dengan jadwal aktif atau reschedule lain).
     */
    public function checkSlotAvailability(
        string|Lapangan $lapangan,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?string $excludeJadwalId = null
    ): bool {
        $lapanganId = $lapangan instanceof Lapangan ? $lapangan->id : $lapangan;
        $jamMulaiFormatted = substr($jamMulai, 0, 5) . ':00';
        $jamSelesaiFormatted = in_array(substr($jamSelesai, 0, 5), ['00:00', '24:00'], true)
            ? '24:00:00'
            : substr($jamSelesai, 0, 5) . ':00';

        // 1. Cek bentrok dengan jadwal aktif
        $conflictJadwal = Jadwal::query()
            ->where('lapangan_id', $lapanganId)
            ->whereDate('tanggal', $tanggal)
            ->whereNotIn('status', ['dibatalkan', 'cancelled', 'rejected'])
            ->when($excludeJadwalId, fn ($q) => $q->where('id', '!=', $excludeJadwalId))
            ->where(function ($query) use ($jamMulaiFormatted, $jamSelesaiFormatted) {
                $query->where('jam_mulai', '<', $jamSelesaiFormatted)
                    ->where('jam_selesai', '>', $jamMulaiFormatted);
            })
            ->exists();

        if ($conflictJadwal) {
            return false;
        }

        // 2. Cek bentrok dengan pengajuan reschedule lain yang masih berstatus 'diajukan'
        $conflictReschedule = Reschedule::query()
            ->where('status', 'diajukan')
            ->whereDate('tanggal_baru', $tanggal)
            ->whereHas('jadwal', function ($q) use ($lapanganId, $excludeJadwalId) {
                $q->where('lapangan_id', $lapanganId)
                    ->when($excludeJadwalId, fn ($sq) => $sq->where('id', '!=', $excludeJadwalId));
            })
            ->where(function ($query) use ($jamMulaiFormatted, $jamSelesaiFormatted) {
                $query->where('jam_mulai_baru', '<', $jamSelesaiFormatted)
                    ->where('jam_selesai_baru', '>', $jamMulaiFormatted);
            })
            ->exists();

        return ! $conflictReschedule;
    }

    /**
     * Memproses pengajuan reschedule dari pelanggan.
     */
    public function requestReschedule(
        Jadwal $jadwal,
        string $tanggalBaru,
        string $jamMulaiBaru,
        string $jamSelesaiBaru,
        string $alasan
    ): Reschedule {
        $eligibility = $this->checkRescheduleEligibility($jadwal);
        if (! $eligibility['eligible']) {
            throw ValidationException::withMessages([
                'reschedule' => $eligibility['reason'],
            ]);
        }

        // Validasi waktu tujuan tidak boleh berada di masa lalu dan wajib berjarak minimal 24 jam dari sekarang
        $targetKickoff = Carbon::parse($tanggalBaru . ' ' . substr($jamMulaiBaru, 0, 5));
        if ($targetKickoff->isPast()) {
            throw ValidationException::withMessages([
                'tanggal_baru' => 'Waktu jadwal baru tidak boleh berada di masa lalu.',
            ]);
        }

        $hoursUntilTarget = now()->diffInHours($targetKickoff, false);
        if ($hoursUntilTarget < self::H_MINUS_HOURS) {
            throw ValidationException::withMessages([
                'jam_mulai_baru' => 'Slot jadwal baru wajib berjarak minimal ' . self::H_MINUS_HOURS . ' jam dari waktu saat ini agar kasir memiliki waktu untuk meninjau pengajuan.',
            ]);
        }

        // Cek ketersediaan slot tujuan
        $available = $this->checkSlotAvailability(
            $jadwal->lapangan_id,
            $tanggalBaru,
            $jamMulaiBaru,
            $jamSelesaiBaru,
            $jadwal->id
        );

        if (! $available) {
            throw ValidationException::withMessages([
                'slot' => 'Slot waktu tujuan pada tanggal tersebut sudah tidak tersedia atau sedang diajukan oleh pemesan lain.',
            ]);
        }

        return DB::transaction(function () use ($jadwal, $tanggalBaru, $jamMulaiBaru, $jamSelesaiBaru, $alasan) {
            return Reschedule::create([
                'jadwal_id' => $jadwal->id,
                'tanggal_baru' => $tanggalBaru,
                'jam_mulai_baru' => substr($jamMulaiBaru, 0, 5) . ':00',
                'jam_selesai_baru' => substr($jamSelesaiBaru, 0, 5) . ':00',
                'status' => 'diajukan',
                'alasan_pengajuan' => $alasan,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Menyetujui permohonan reschedule (Kasir/Admin).
     */
    public function approveReschedule(Reschedule $reschedule, ?string $catatan = null): Jadwal
    {
        return DB::transaction(function () use ($reschedule, $catatan) {
            $lockedReschedule = Reschedule::with('jadwal')
                ->lockForUpdate()
                ->findOrFail($reschedule->id);

            if ($lockedReschedule->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'reschedule' => 'Pengajuan reschedule ini sudah diproses sebelumnya.',
                ]);
            }

            $jadwal = $lockedReschedule->jadwal;
            if (! $jadwal) {
                throw ValidationException::withMessages([
                    'jadwal' => 'Jadwal terkait tidak ditemukan.',
                ]);
            }

            // Validasi kembali ketersediaan slot tujuan saat approval
            $isStillAvailable = $this->checkSlotAvailability(
                $jadwal->lapangan_id,
                $lockedReschedule->tanggal_baru->toDateString(),
                $lockedReschedule->jam_mulai_baru,
                $lockedReschedule->jam_selesai_baru,
                $jadwal->id
            );

            if (! $isStillAvailable) {
                throw ValidationException::withMessages([
                    'slot' => 'Slot tujuan bentrok dengan jadwal lain yang baru saja dikonfirmasi.',
                ]);
            }

            $jadwal->update([
                'tanggal' => $lockedReschedule->tanggal_baru,
                'jam_mulai' => $lockedReschedule->jam_mulai_baru,
                'jam_selesai' => $lockedReschedule->jam_selesai_baru,
                'catatan_kasir' => $catatan ?? ('Reschedule disetujui kasir pada ' . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB'),
            ]);

            $lockedReschedule->update([
                'status' => 'disetujui',
            ]);

            return $jadwal->fresh();
        }, 3);
    }

    /**
     * Menolak permohonan reschedule (Kasir/Admin).
     */
    public function rejectReschedule(Reschedule $reschedule, ?string $alasan = null): Reschedule
    {
        return DB::transaction(function () use ($reschedule, $alasan) {
            $lockedReschedule = Reschedule::with('jadwal')
                ->lockForUpdate()
                ->findOrFail($reschedule->id);

            if ($lockedReschedule->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'reschedule' => 'Pengajuan reschedule ini sudah diproses sebelumnya.',
                ]);
            }

            $lockedReschedule->update([
                'status' => 'ditolak',
            ]);

            if ($alasan && $lockedReschedule->jadwal) {
                $oldNote = $lockedReschedule->jadwal->catatan_kasir;
                $lockedReschedule->jadwal->update([
                    'catatan_kasir' => ($oldNote ? $oldNote . ' | ' : '') .
                        'Reschedule ditolak: ' . $alasan . ' (' . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB)',
                ]);
            }

            return $lockedReschedule->fresh();
        });
    }

    /**
     * Kalkulasi otomatis penalti pembatalan sewa (Auto-Penalty Policy).
     *
     * @return array{
     *     hours_until_kickoff: float|int,
     *     is_h_minus_24: bool,
     *     policy_rule: string,
     *     policy_badge: string,
     *     total_price: float,
     *     total_paid: float,
     *     penalty_percentage: int,
     *     penalty_amount: float,
     *     estimated_refund: float,
     *     policy_description: string
     * }
     */
    public function calculateCancellationPenalty(Jadwal $jadwal): array
    {
        $kickoff = $this->getKickoffDateTime($jadwal);
        $hoursUntilKickoff = now()->diffInHours($kickoff, false);
        $totalPrice = (float) $jadwal->harga_disepakati;

        // Hitung akumulasi pembayaran terverifikasi
        $totalPaid = (float) $jadwal->transaksis()
            ->whereIn('status', ['berhasil', 'lunas', 'settlement', 'capture'])
            ->sum('jumlah');

        // Fallback jika status pembayaran aktif namun record transaksi pending/offline
        if ($totalPaid <= 0) {
            if ($jadwal->status_pembayaran === 'dp_terverifikasi') {
                $totalPaid = round($totalPrice * 0.5);
            } elseif (in_array($jadwal->status_pembayaran, ['lunas_online', 'lunas_kasir'], true)) {
                $totalPaid = $totalPrice;
            }
        }

        $isUnpaid = $jadwal->status_pembayaran === 'belum_bayar' || $totalPaid <= 0;

        if ($isUnpaid) {
            return [
                'hours_until_kickoff' => $hoursUntilKickoff,
                'is_h_minus_24' => false,
                'policy_rule' => 'unpaid',
                'policy_badge' => 'Bebas Penalti',
                'total_price' => $totalPrice,
                'total_paid' => 0.0,
                'penalty_percentage' => 0,
                'penalty_amount' => 0.0,
                'estimated_refund' => 0.0,
                'policy_description' => 'Pesanan belum dibayar, pembatalan tanpa dikenakan denda penalti.',
            ];
        }

        // Kasus 1: Pembatalan mendadak di bawah 24 jam (< H-24 Jam)
        if ($hoursUntilKickoff < self::H_MINUS_HOURS) {
            // Kebijakan H-24: Hangus DP (50% dari harga total sewa / 100% uang muka)
            $dpAmount = round($totalPrice * 0.5);
            $penaltyAmount = (float) $dpAmount;
            $estimatedRefund = (float) max(0.0, $totalPaid - $penaltyAmount);

            return [
                'hours_until_kickoff' => $hoursUntilKickoff,
                'is_h_minus_24' => true,
                'policy_rule' => 'under_24h',
                'policy_badge' => 'Hangus DP (H < 24 Jam)',
                'total_price' => $totalPrice,
                'total_paid' => $totalPaid,
                'penalty_percentage' => 50,
                'penalty_amount' => $penaltyAmount,
                'estimated_refund' => $estimatedRefund,
                'policy_description' => 'Pembatalan dilakukan kurang dari 24 jam sebelum jadwal bermain (< H-24 Jam). Sesuai kebijakan venue, uang muka (DP 50% / Rp ' . number_format($dpAmount, 0, ',', '.') . ') hangus sebagai kompensasi slot terkunci.',
            ];
        }

        // Kasus 2: Pembatalan terencana minimal 24 jam sebelumnya (>= H-24 Jam)
        // Dikenakan biaya penalti administrasi pelepasan slot 10%
        $adminFeePercentage = 10;
        $penaltyAmount = (float) round($totalPrice * ($adminFeePercentage / 100));
        $estimatedRefund = (float) max(0.0, $totalPaid - $penaltyAmount);

        return [
            'hours_until_kickoff' => $hoursUntilKickoff,
            'is_h_minus_24' => false,
            'policy_rule' => 'over_24h',
            'policy_badge' => 'Refund Parsial (H >= 24 Jam)',
            'total_price' => $totalPrice,
            'total_paid' => $totalPaid,
            'penalty_percentage' => $adminFeePercentage,
            'penalty_amount' => $penaltyAmount,
            'estimated_refund' => $estimatedRefund,
            'policy_description' => 'Pembatalan memenuhi ketentuan minimal H-24 jam. Dikenakan biaya penalti administrasi pelepasan slot ' . $adminFeePercentage . '% (Rp ' . number_format($penaltyAmount, 0, ',', '.') . '), estimasi dana yang dapat dikembalikan: Rp ' . number_format($estimatedRefund, 0, ',', '.') . '.',
        ];
    }

    /**
     * Memproses pembatalan sewa dan melepaskan slot jadwal kembali ke publik.
     *
     * @return array{
     *     jadwal: Jadwal,
     *     penalty: array
     * }
     */
    public function cancelBooking(
        Jadwal $jadwal,
        ?string $alasan = null,
        ?Pelanggan $cancelledBy = null
    ): array {
        if (in_array($jadwal->status, ['dibatalkan', 'cancelled', 'rejected', 'selesai'], true)) {
            throw ValidationException::withMessages([
                'cancellation' => 'Jadwal ini sudah tidak aktif atau telah selesai.',
            ]);
        }

        if (!empty($jadwal->checked_in_at)) {
            throw ValidationException::withMessages([
                'cancellation' => 'Penyewa sudah melakukan check-in di venue, tiket tidak dapat dibatalkan.',
            ]);
        }

        return DB::transaction(function () use ($jadwal, $alasan, $cancelledBy) {
            $penaltyData = $this->calculateCancellationPenalty($jadwal);

            $cancellerName = $cancelledBy?->nama ?? ($jadwal->pelanggan?->nama ?? 'Pelanggan');

            // Format catatan audit riwayat
            $cancellationNote = "Dibatalkan oleh {$cancellerName} pada " . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' .
                ". Penalti: Rp " . number_format($penaltyData['penalty_amount'], 0, ',', '.') .
                " (" . $penaltyData['policy_badge'] . ")." .
                ($alasan ? " Alasan: {$alasan}" : "");

            $oldCatatan = $jadwal->catatan_kasir;
            $updatedCatatan = $oldCatatan ? ($oldCatatan . ' | ' . $cancellationNote) : $cancellationNote;

            // Update jadwal menjadi dibatalkan
            // Mengubah status menjadi 'dibatalkan' otomatis melepaskan slot jadwal
            // dari PostgreSQL exclusion constraint (WHERE status <> 'dibatalkan')
            $jadwal->update([
                'status' => 'dibatalkan',
                'denda_pembatalan' => $penaltyData['penalty_amount'],
                'catatan_kasir' => $updatedCatatan,
            ]);

            // Jika ada pengajuan reschedule yang masih 'diajukan', batalkan
            $jadwal->reschedules()->where('status', 'diajukan')->update([
                'status' => 'ditolak',
            ]);

            // Jika ada transaksi pending, tandai gagal
            $jadwal->transaksis()->where('status', 'pending')->update([
                'status' => 'gagal',
            ]);

            return [
                'jadwal' => $jadwal->fresh(),
                'penalty' => $penaltyData,
            ];
        });
    }

    /**
     * Mengambil Carbon instance waktu kickoff jadwal bermain.
     */
    public function getKickoffDateTime(Jadwal $jadwal): Carbon
    {
        $tanggal = $jadwal->tanggal instanceof Carbon
            ? $jadwal->tanggal->toDateString()
            : (string) $jadwal->tanggal;

        $jamMulai = substr((string) $jadwal->jam_mulai, 0, 5);

        return Carbon::parse($tanggal . ' ' . $jamMulai);
    }
}
