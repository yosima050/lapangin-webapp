<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardwareCheckinAndPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected Pelanggan $kasir;
    protected Pelanggan $pelanggan;
    protected Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = Pelanggan::create([
            'nama' => 'Kasir Hardware POS',
            'email' => 'kasir.hardware@lapangin.com',
            'no_hp' => '081299998888',
            'password_hash' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'no_hp' => '081277776666',
            'password_hash' => bcrypt('password123'),
            'role' => 'pelanggan',
        ]);

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Futsal 1 - Vinyl Pro',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '23:00:00',
            'deskripsi' => 'Lantai vinyl interlock',
        ]);
    }

    public function test_public_pages_can_be_accessed(): void
    {
        $this->get(route('kontak'))->assertOk()->assertSee('Lokasi &amp; Jam Operasional', false);
        $this->get(route('bantuan'))->assertOk()->assertSee('Pusat Bantuan &amp; FAQ', false);
        $this->get(route('kebijakan.reschedule'))->assertOk()->assertSee('Kebijakan Reschedule');
        $this->get(route('syarat.ketentuan'))->assertOk()->assertSee('Ketentuan Layanan');
        $this->get(route('kebijakan.privasi'))->assertOk()->assertSee('Kebijakan Privasi');
    }

    public function test_kasir_hardware_checkin_screen_accessible(): void
    {
        $response = $this->actingAs($this->kasir)->get(route('kasir.checkin.index'));
        $response->assertOk();
        $response->assertSee('Scan Tiket &amp; Validasi Pelunasan', false);
        $response->assertSee('barcode-input');
    }

    public function test_kasir_can_process_ticket_via_hardware_scanner_and_redirect_to_thermal_struk(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => 'Budi Santoso',
            'no_wa_tamu' => '081277776666',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '14:00:00',
            'jam_selesai' => '16:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'pending',
            'kode_qr' => 'LPGN-TEST-12345',
            'token_manual' => 'TKN123',
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 100000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        // Process check-in via barcode scanner
        $response = $this->actingAs($this->kasir)->post(route('kasir.checkin.process'), [
            'code' => 'LPGN-TEST-12345',
            'cash_received' => 100000,
        ]);

        $response->assertRedirect(route('kasir.struk', $jadwal->id));

        $jadwal->refresh();
        $this->assertNotNull($jadwal->checked_in_at);
        $this->assertEquals('terverifikasi', $jadwal->status);
        $this->assertEquals('lunas_kasir', $jadwal->status_pembayaran);

        // Pelunasan transaction created
        $pelunasan = Transaksi::where('jadwal_id', $jadwal->id)->where('jenis', 'pelunasan')->first();
        $this->assertNotNull($pelunasan);
        $this->assertEquals(100000, $pelunasan->jumlah);

        // Thermal struk view renders properly
        $strukResponse = $this->actingAs($this->kasir)->get(route('kasir.struk', $jadwal->id));
        $strukResponse->assertOk();
        $strukResponse->assertSee('LAPANGIN ARENA');
        $strukResponse->assertSee('LPGN-TEST-12345');
        $strukResponse->assertSee('Budi Santoso');
        $strukResponse->assertSee('window.print()', false);
    }

    public function test_kasir_prevents_duplicate_checkin(): void
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
            'checked_in_at' => now()->subHour(),
            'kode_qr' => 'LPGN-ALREADY-USED',
            'token_manual' => 'USED99',
        ]);

        $response = $this->actingAs($this->kasir)->post(route('kasir.checkin.process'), [
            'code' => 'LPGN-ALREADY-USED',
        ]);

        $response->assertSessionHas('error');
    }
}
