<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Lapangan;
use Carbon\Carbon;
use JsonException;

class JadwalService
{
    /**
     * Return hourly availability for a field on a given date.
     *
     * @return array<string, mixed>
     */
    public function getSlotStatus(string $lapanganId, string $tanggal): array
    {
        $lapangan = Lapangan::findOrFail($lapanganId);
        $daftarJadwal = Jadwal::query()
            ->where('lapangan_id', $lapanganId)
            ->whereDate('tanggal', $tanggal)
            ->get();

        $jamBuka = $this->parseTime($lapangan->jam_buka);
        $jamTutup = $this->parseTime($lapangan->jam_tutup);
        $slots = [];

        for ($slotMulai = $jamBuka->copy(); $slotMulai->lt($jamTutup); $slotMulai->addHour()) {
            $slotSelesai = $slotMulai->copy()->addHour();

            if ($slotSelesai->gt($jamTutup)) {
                break;
            }

            $jadwalCocok = $daftarJadwal->first(function (Jadwal $jadwal) use ($slotMulai, $slotSelesai): bool {
                $jadwalMulai = $this->parseTime($jadwal->jam_mulai);
                $jadwalSelesai = $this->parseTime($jadwal->jam_selesai);

                return $slotMulai->lt($jadwalSelesai) && $slotSelesai->gt($jadwalMulai);
            });

            $status = $jadwalCocok === null
                ? 'tersedia'
                : $this->resolveStatus($jadwalCocok);

            $slots[] = [
                'jam_mulai' => $slotMulai->format('H:i'),
                'jam_selesai' => $slotSelesai->format('H:i'),
                'jam_label' => $slotMulai->format('H:i').' - '.$slotSelesai->format('H:i'),
                'status' => $status,
                'is_tersedia' => $status === 'tersedia',
                'jadwal_id' => $jadwalCocok?->id,
                'nama_penyewa' => $jadwalCocok?->nama_tamu,
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

        return in_array($status, ['pending', 'menunggu_pembayaran'], true)
            || $statusPembayaran === 'belum_bayar'
            ? 'pending'
            : 'dibooking';
    }

    private function parseTime(mixed $time): Carbon
    {
        return Carbon::createFromFormat('H:i', substr((string) $time, 0, 5));
    }
}
