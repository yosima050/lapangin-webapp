<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganProfileAndRiwayatPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Pelanggan $pelanggan;

    private Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelanggan = Pelanggan::factory()->create([
            'nama' => 'Dimas Prasetyo',
            'email' => 'dimas.prasetyo@sports.id',
            'no_hp' => '081289341209',
            'role' => 'pelanggan',
            'email_verified_at' => now(),
        ]);

        $this->lapangan = Lapangan::create([
            'nama' => 'Gelanggang Utama Padel A',
            'kategori' => 'Padel',
            'jam_buka' => '07:00',
            'jam_tutup' => '23:00',
            'deskripsi' => 'Lapangan berstandar turnamen internasional',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '07:00',
            'jam_selesai' => '23:00',
            'harga' => 150000,
        ]);
    }

    public function test_pelanggan_profile_page_displays_account_settings_view(): void
    {
        $response = $this->actingAs($this->pelanggan)->get(route('pelanggan.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun &amp; Profil Pelanggan', false);
        $response->assertSee('PRO PLAYER ID');
        $response->assertSee('Dimas Prasetyo');
        $response->assertSee('dimas.prasetyo@sports.id');
        $response->assertSee('Informasi Pribadi');
        $response->assertSee('Ubah Kata Sandi');
        $response->assertSee('Simpan Perubahan Profil');
        $response->assertSee('Perbarui Kata Sandi');
    }

    public function test_pelanggan_can_update_profile_and_phone(): void
    {
        $response = $this->actingAs($this->pelanggan)->patch(route('profile.update'), [
            'name' => 'Dimas Prasetyo Update',
            'email' => 'dimas.update@sports.id',
            'no_hp' => '081299998888',
        ]);

        $response->assertSessionHasNoErrors();

        $this->pelanggan->refresh();
        $this->assertEquals('Dimas Prasetyo Update', $this->pelanggan->nama);
        $this->assertEquals('dimas.update@sports.id', $this->pelanggan->email);
        $this->assertEquals('081299998888', $this->pelanggan->no_hp);
    }

    public function test_riwayat_booking_displays_bayar_sekarang_for_unpaid_booking(): void
    {
        $unpaidJadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status' => 'pending',
            'status_pembayaran' => 'belum_bayar',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)->get(route('pelanggan.booking.index'));

        $response->assertStatus(200);
        $response->assertSee('Belum Dibayar');
        $response->assertSee('Bayar Sekarang');
        $response->assertSee('Buka Checkout Penuh');
        $response->assertSee('Selesaikan Pembayaran Booking');
        $response->assertSee('https://app.sandbox.midtrans.com/snap/snap.js', false);
    }

    public function test_riwayat_booking_displays_tiket_for_dp_and_lunas_bookings(): void
    {
        // 1. Jadwal dengan DP terverifikasi
        $dpJadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '14:00',
            'jam_selesai' => '16:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status' => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'kode_qr' => 'LPNG-QR-DP-123456',
            'token_manual' => 'DP1234',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)->get(route('pelanggan.booking.index'));

        $response->assertStatus(200);
        $response->assertSee('DP 50% Terverifikasi (Siap Main)');
        $response->assertSee('Lihat E-Tiket Masuk &amp; QR', false);
    }
}
