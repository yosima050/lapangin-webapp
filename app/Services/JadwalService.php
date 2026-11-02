<?php

namespace App\Services;

use App\Models\HargaKhusus;
use App\Models\Jadwal;
use App\Models\Lapangan;
use Carbon\Carbon;
use JsonException;

class JadwalService
{
    /**
     * Return hourly availability and exact pricing for a field on a given date.
     *
     * @return array<string, mixed>
     */
    public function getSlotStatus(string $lapanganId, string $tanggal): array
    {
        $lapangan = Lapangan::with('hargaMasters')->findOrFail($lapanganId);
        Jadwal::autoCancelExpiredPendingBookings();

        $daftarJadwal = Jadwal::query()
            ->where('lapangan_id', $lapanganId)
            ->whereDate('tanggal', $tanggal)
            ->whereNotIn('status', ['dibatalkan', 'cancelled', 'rejected'])
            ->get();

        $hargaKhususAktif = HargaKhusus::query()
            ->where('lapangan_id', $lapanganId)
            ->whereDate('tanggal', $tanggal)
            ->where('is_active', true)
            ->get();

        $baseDate = Carbon::parse($tanggal, 'Asia/Jakarta')->startOfDay();
        $isWeekend = in_array($baseDate->dayOfWeek, [0, 6], true);

        $hargaWeekday = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekday')
            ->first()?->harga;

        $hargaWeekend = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekend')
            ->first()?->harga;

        $defaultBaseRate = (float) ($isWeekend ? ($hargaWeekend ?? 175000) : ($hargaWeekday ?? 125000));
        $defaultPrimeRate = (float) ($defaultBaseRate + 40000);

        $jamBuka = $baseDate->copy()->setTimeFromTimeString(substr($lapangan->jam_buka, 0, 5));
        $jamTutupStr = substr($lapangan->jam_tutup, 0, 5);
        $jamTutup = in_array($jamTutupStr, ['24:00', '00:00'], true)
            ? $baseDate->copy()->addDay()->startOfDay()
            : $baseDate->copy()->setTimeFromTimeString($jamTutupStr);
        $slots = [];

        $now = Carbon::now('Asia/Jakarta');
        $isToday = $tanggal === $now->toDateString();
        $isPastDate = $tanggal < $now->toDateString();
        $currentTime = $now->format('H:i');

        for ($slotMulai = $jamBuka->copy(); $slotMulai->lt($jamTutup); $slotMulai->addHour()) {
            $slotSelesai = $slotMulai->copy()->addHour();

            if ($slotSelesai->gt($jamTutup)) {
                break;
            }

            $jadwalCocok = $daftarJadwal->first(function (Jadwal $jadwal) use ($baseDate, $slotMulai, $slotSelesai): bool {
                $jadwalMulai = $baseDate->copy()->setTimeFromTimeString(substr($jadwal->jam_mulai, 0, 5));
                $jSelesaiStr = substr($jadwal->jam_selesai, 0, 5);
                $jadwalSelesai = in_array($jSelesaiStr, ['24:00', '00:00'], true)
                    ? $baseDate->copy()->addDay()->startOfDay()
                    : $baseDate->copy()->setTimeFromTimeString($jSelesaiStr);

                return $slotMulai->lt($jadwalSelesai) && $slotSelesai->gt($jadwalMulai);
            });

            $slotMulaiStr = $slotMulai->format('H:i');
            $slotSelesaiStr = $slotSelesai->format('H:i');
            $isPastSlot = $isPastDate || ($isToday && $slotMulaiStr <= $currentTime);

            $status = $jadwalCocok === null
                ? 'tersedia'
                : $this->resolveStatus($jadwalCocok);

            if ($isPastSlot) {
                if ($status === 'tersedia') {
                    $status = 'lewat_jam';
                }
                $isTersedia = false;
            } else {
                $isTersedia = ($status === 'tersedia');
            }

            // Hitung harga slot: cek apakah ada HargaKhusus aktif yang mencakup jam ini
            $customPrice = $hargaKhususAktif->first(function (HargaKhusus $hk) use ($baseDate, $slotMulai, $slotSelesai): bool {
                $hkMulai = $baseDate->copy()->setTimeFromTimeString(substr($hk->jam_mulai, 0, 5));
                $hkSelesaiStr = substr($hk->jam_selesai, 0, 5);
                $hkSelesai = in_array($hkSelesaiStr, ['24:00', '00:00'], true)
                    ? $baseDate->copy()->addDay()->startOfDay()
                    : $baseDate->copy()->setTimeFromTimeString($hkSelesaiStr);

                return $slotMulai->gte($hkMulai) && $slotSelesai->lte($hkSelesai);
            });

            if ($customPrice !== null) {
                $slotHarga = (float) $customPrice->harga;
                $isHargaKhusus = true;
                $keteranganHarga = $customPrice->keterangan ?: 'Harga Khusus';
            } else {
                $hour = (int) $slotMulai->format('H');
                $slotHarga = (float) ($hour >= 18 ? $defaultPrimeRate : $defaultBaseRate);
                $isHargaKhusus = false;
                $keteranganHarga = null;
            }

            $slots[] = [
                'jam_mulai' => $slotMulaiStr,
                'jam_selesai' => $slotSelesaiStr,
                'jam_label' => $slotMulaiStr.' - '.$slotSelesaiStr,
                'status' => $status,
                'is_tersedia' => $isTersedia,
                'jadwal_id' => $jadwalCocok?->id,
                'nama_penyewa' => $jadwalCocok?->nama_tamu,
                'harga' => $slotHarga,
                'is_harga_khusus' => $isHargaKhusus,
                'keterangan_harga' => $keteranganHarga,
            ];
        }

        return [
            'lapangan_id' => $lapangan->id,
            'lapangan_nama' => $lapangan->nama,
            'kategori' => $lapangan->kategori,
            'tanggal' => $tanggal,
            'jam_operasional' => [
                'jam_buka' => $jamBuka->format('H:i'),
                'jam_tutup' => $jamTutup->format('H:i'),
            ],
            'slots' => $slots,
            'has_harga_khusus' => $hargaKhususAktif->isNotEmpty(),
            'harga_termurah' => count($slots) > 0 ? collect($slots)->min('harga') : $defaultBaseRate,
            'base_rate' => $defaultBaseRate,
            'prime_rate' => $defaultPrimeRate,
        ];
    }

    /**
     * @throws JsonException
     */
    public function getSlotStatusJson(string $lapanganId, string $tanggal): string
    {
        return json_encode($this->getSlotStatus($lapanganId, $tanggal), JSON_THROW_ON_ERROR);
    }

    private function resolveStatus(Jadwal $jadwal): string
    {
        $status = strtolower((string) $jadwal->status);
        $statusPembayaran = strtolower((string) $jadwal->status_pembayaran);

        if (in_array($status, ['dibatalkan', 'cancelled', 'rejected'], true)) {
            return 'tersedia';
        }

        return in_array($status, ['pending', 'menunggu_pembayaran'], true)
            || $statusPembayaran === 'belum_bayar'
            ? 'pending'
            : 'dibooking';
    }

    private function parseTime(mixed $time): Carbon
    {
        return Carbon::createFromFormat('H:i', substr((string) $time, 0, 5), 'Asia/Jakarta');
    }
}
