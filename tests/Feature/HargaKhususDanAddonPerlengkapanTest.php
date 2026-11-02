<?php

namespace Tests\Feature;

use App\Models\HargaKhusus;
use App\Models\HargaMaster;
use App\Models\Lapangan;
use App\Models\Perlengkapan;
use App\Services\JadwalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HargaKhususDanAddonPerlengkapanTest extends TestCase
{
    use RefreshDatabase;

    public function test_harga_khusus_terdeteksi_pada_jadwal_service_dan_katalog(): void
    {
        $lapangan = Lapangan::forceCreate([
            'nama' => 'Lapangan Futsal Test',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '22:00:00',
        ]);

        HargaMaster::forceCreate([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '22:00:00',
            'harga' => 120000,
        ]);

        $targetDate = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();

        // Buat aturan harga khusus untuk jam 14:00 - 16:00
        $hargaKhusus = HargaKhusus::forceCreate([
            'lapangan_id' => $lapangan->id,
            'tanggal' => $targetDate,
            'jam_mulai' => '14:00',
            'jam_selesai' => '16:00',
            'harga' => 88888,
            'keterangan' => 'Promo Diskon Flash',
            'is_active' => true,
        ]);

        $jadwalService = app(JadwalService::class);
        $status = $jadwalService->getSlotStatus((string) $lapangan->id, $targetDate);

        $this->assertTrue($status['has_harga_khusus']);
        
        $slot14 = collect($status['slots'])->first(function ($s) {
            return str_starts_with($s['jam_mulai'], '14:00');
        });

        $this->assertNotNull($slot14);
        $this->assertTrue((bool) $slot14['is_harga_khusus']);
        $this->assertEquals(88888, (int) $slot14['harga']);
        $this->assertEquals('Promo Diskon Flash', $slot14['keterangan_harga']);

        // Uji response katalog show JSON (AJAX)
        $response = $this->getJson(route('katalog.show', [
            'id' => $lapangan->id,
            'tanggal' => $targetDate,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'tanggal' => $targetDate,
        ]);

        $data = $response->json();
        $slotSiang14 = collect($data['slotsSiang'])->first(function ($s) {
            return str_starts_with($s['jam_mulai'], '14:00');
        });
        $this->assertNotNull($slotSiang14);
        $this->assertTrue($slotSiang14['is_harga_khusus']);
        $this->assertEquals(88888, (int) $slotSiang14['harga']);
    }

    public function test_addon_perlengkapan_hanya_tampil_pada_lapangan_terpilih(): void
    {
        $lapanganA = Lapangan::forceCreate([
            'nama' => 'Lapangan Badminton A',
            'kategori' => 'Badminton',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '22:00:00',
        ]);

        $lapanganB = Lapangan::forceCreate([
            'nama' => 'Lapangan Futsal B',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '22:00:00',
        ]);

        // 1. Perlengkapan khusus Lapangan A saja (Raket)
        $perlengkapanKhususA = Perlengkapan::forceCreate([
            'nama' => 'Raket Yonex Khusus Lapangan A',
            'deskripsi' => 'Raket khusus lapangan badminton A',
            'harga' => 25000,
            'is_active' => true,
        ]);
        $perlengkapanKhususA->lapangans()->attach($lapanganA->id);

        // 2. Perlengkapan global (semua lapangan)
        $perlengkapanGlobal = Perlengkapan::forceCreate([
            'nama' => 'Handuk Olahraga Universal',
            'deskripsi' => 'Tersedia di semua lapangan',
            'harga' => 10000,
            'is_active' => true,
        ]);

        // Cek halaman show Lapangan A
        $responseA = $this->get(route('katalog.show', ['id' => $lapanganA->id]));
        $responseA->assertOk();
        $responseA->assertSee('Raket Yonex Khusus Lapangan A');
        $responseA->assertSee('Handuk Olahraga Universal');

        // Cek halaman show Lapangan B
        $responseB = $this->get(route('katalog.show', ['id' => $lapanganB->id]));
        $responseB->assertOk();
        $responseB->assertDontSee('Raket Yonex Khusus Lapangan A');
        $responseB->assertSee('Handuk Olahraga Universal');
    }
}
