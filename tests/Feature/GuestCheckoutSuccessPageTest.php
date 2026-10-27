<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestCheckoutSuccessPageTest extends TestCase
{
    public function test_guest_can_access_checkout_success_page_without_login(): void
    {
        $lapangan = Lapangan::firstOrCreate(
            ['nama' => 'Lapangan Test Success'],
            [
                'kategori' => 'Futsal',
                'deskripsi' => 'Deskripsi Lapangan',
                'foto' => 'lapangan/test.jpg',
            ]
        );

        $orderId = 'LPNG-TEST-' . strtoupper(Str::random(6));

        $jadwal = Jadwal::create([
            'lapangan_id' => $lapangan->id,
            'nama_tamu' => 'Tamu Percobaan',
            'no_wa_tamu' => '081234567899',
            'tanggal' => now()->addDay()->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'token_manual' => 'TKN123',
            'kode_qr' => 'LPNG-QR-TKN123',
        ]);

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'order_id' => $orderId,
            'referensi_midtrans' => $orderId,
            'jumlah' => 150000,
            'jenis' => 'full',
            'metode' => 'online',
            'status' => 'berhasil',
        ]);

        // Akses sebagai tamu (belum login)
        $response = $this->get(route('checkout.success', ['orderId' => $orderId]));

        $response->assertStatus(200);
        $response->assertSee('Pemesanan Lapangan Berhasil!');
        $response->assertSee('TKN123');
        $response->assertSee('Tamu Percobaan');
        $response->assertSee('081234567899');
        $response->assertSee(route('register', ['no_hp' => '081234567899', 'name' => 'Tamu Percobaan']));
    }
}

