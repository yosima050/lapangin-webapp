<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLaporanRekonsiliasiTest extends TestCase
{
    use RefreshDatabase;

    private Pelanggan $admin;
    private Pelanggan $pelanggan;
    private Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pelanggan::create([
            'nama' => 'Admin Keuangan LapangIn',
            'email' => 'admin.keuangan@lapangin.id',
            'no_hp' => '081211112222',
            'password_hash' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama' => 'Bima Santoso',
            'email' => 'bima.santoso@gmail.com',
            'no_hp' => '081233334444',
            'password_hash' => bcrypt('password'),
            'role' => 'pelanggan',
            'email_verified_at' => now(),
        ]);

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Futsal A',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '24:00',
            'deskripsi' => 'Lapangan futsal vinyl standar',
        ]);
    }

    public function test_non_admin_cannot_access_laporan(): void
    {
        // 1. Guest redirected to login
        $guestResponse = $this->get(route('admin.laporan.index'));
        $guestResponse->assertRedirect(route('login'));

        // 2. Pelanggan biasa ditolak (403 Forbidden)
        $pelangganResponse = $this->actingAs($this->pelanggan)->get(route('admin.laporan.index'));
        $pelangganResponse->assertStatus(403);
    }

    public function test_admin_can_view_laporan_with_reconciliation_totals(): void
    {
        $jadwal1 = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'created_at' => now(),
        ]);

        // Transaksi 1: Online Midtrans Rp 200.000
        Transaksi::create([
            'jadwal_id' => $jadwal1->id,
            'jenis' => 'full',
            'jumlah' => 200000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $jadwal2 = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Tamu Kasir Onsite',
            'no_wa_tamu' => '081299990000',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '16:00:00',
            'jam_selesai' => '18:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_kasir',
            'status' => 'selesai',
            'created_at' => now(),
        ]);

        // Transaksi 2: Kas Fisik Kasir (Tunai) Rp 150.000
        Transaksi::create([
            'jadwal_id' => $jadwal2->id,
            'jenis' => 'full',
            'jumlah' => 150000,
            'metode' => 'tunai',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', ['periode' => 'hari_ini']));

        $response->assertStatus(200);
        $response->assertSee('Laporan &amp; Rekonsiliasi Finansial', false);
        // Total Omset Rp 350.000
        $response->assertSee('350.000');
        // Online Midtrans Rp 200.000
        $response->assertSee('200.000');
        // Kas Fisik Tunai Rp 150.000
        $response->assertSee('150.000');
        // Unduh PDF button
        $response->assertSee('Unduh Laporan PDF');
    }

    public function test_admin_can_filter_laporan_by_method(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '18:00:00',
            'jam_selesai' => '20:00:00',
            'harga_disepakati' => 100000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'full',
            'jumlah' => 100000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        // Filter saluran tunai (harus kosong)
        $responseTunai = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'periode' => 'hari_ini',
            'metode' => 'tunai',
        ]));

        $responseTunai->assertStatus(200);
        $responseTunai->assertSee('Tidak ada transaksi yang sesuai dengan filter periode ini');

        // Filter saluran online (harus ada)
        $responseOnline = $this->actingAs($this->admin)->get(route('admin.laporan.index', [
            'periode' => 'hari_ini',
            'metode' => 'online',
        ]));

        $responseOnline->assertStatus(200);
        $responseOnline->assertSee('MIDTRANS ONLINE');
    }

    public function test_admin_can_export_laporan_as_pdf(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '14:00:00',
            'jam_selesai' => '16:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'full',
            'jumlah' => 200000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.exportPdf', ['periode' => 'bulan_ini']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }
}
