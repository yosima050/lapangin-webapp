<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Services\JadwalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_available_booked_and_pending_slots(): void
    {
        $lapangan = Lapangan::forceCreate([
            'nama' => 'Lapangan Futsal A',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '12:00:00',
            'created_at' => now(),
        ]);

        $tanggal = Carbon::now()->addDays(2)->format('Y-m-d');

        Jadwal::forceCreate([
            'lapangan_id' => $lapangan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 5000,
            'status_pembayaran' => 'lunas_online',
            'status' => 'dibooking',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        Jadwal::forceCreate([
            'lapangan_id' => $lapangan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 5000,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $result = (new JadwalService)->getSlotStatus($lapangan->id, $tanggal);

        $this->assertSame($tanggal, $result['tanggal']);
        $this->assertCount(4, $result['slots']);
        $this->assertSame(['dibooking', 'pending', 'tersedia', 'tersedia'], array_column($result['slots'], 'status'));
        $this->assertFalse($result['slots'][0]['is_tersedia']);
        $this->assertFalse($result['slots'][1]['is_tersedia']);
        $this->assertTrue($result['slots'][2]['is_tersedia']);
        $this->assertTrue($result['slots'][3]['is_tersedia']);
    }

    public function test_it_marks_past_slots_as_lewat_jam_for_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 14:30:00'));

        $lapangan = Lapangan::forceCreate([
            'nama' => 'Lapangan Test Past Slots',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '18:00:00',
            'created_at' => now(),
        ]);

        $today = Carbon::now()->toDateString();
        $result = (new JadwalService)->getSlotStatus($lapangan->id, $today);

        // Slot 08:00, 09:00, 10:00, 11:00, 12:00, 13:00, 14:00 (<= 14:30) harus lewat_jam
        foreach ($result['slots'] as $slot) {
            if ($slot['jam_mulai'] <= '14:30') {
                $this->assertSame('lewat_jam', $slot['status'], "Slot {$slot['jam_mulai']} harus berstatus lewat_jam");
                $this->assertFalse($slot['is_tersedia'], "Slot {$slot['jam_mulai']} tidak boleh tersedia");
            } else {
                $this->assertSame('tersedia', $slot['status'], "Slot {$slot['jam_mulai']} harus tersedia");
                $this->assertTrue($slot['is_tersedia'], "Slot {$slot['jam_mulai']} harus tersedia");
            }
        }

        Carbon::setTestNow();
    }
}
