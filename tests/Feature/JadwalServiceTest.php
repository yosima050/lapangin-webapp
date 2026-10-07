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
}
