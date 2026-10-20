<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class MidtransCallbackTest extends TestCase
{
    use RefreshDatabase;

    private string $serverKey = 'SB-Mid-server-TEST123456';
    private Lapangan $lapangan;
    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', $this->serverKey);

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Midtrans Test',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Deskripsi lapangan midtrans',
        ]);

        $this->pelanggan = Pelanggan::factory()->create([
            'role' => 'pelanggan',
            'email_verified_at' => now(),
        ]);
    }

    public function test_midtrans_notification_rejects_invalid_signature(): void
    {
        $payload = [
            'order_id' => 'ORDER-12345',
            'status_code' => '200',
            'gross_amount' => '150000.00',
            'signature_key' => 'invalid-signature-key-hash',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature key']);
    }

    public function test_midtrans_notification_handles_settlement_for_dp_successfully(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => '08123456789',
            'email_tamu' => $this->pelanggan->email,
            'tanggal' => now()->addDays(1)->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $orderId = 'ORDER-MID-001';
        $grossAmount = '100000.00';
        $statusCode = '200';

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 100000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => $orderId,
            'referensi_midtrans' => $orderId,
        ]);

        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-trx-uuid-999',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Notification handled']);

        $transaksi->refresh();
        $this->assertEquals('berhasil', $transaksi->status);
        $this->assertEquals('settlement', $transaksi->transaction_status);
        $this->assertEquals('qris', $transaksi->payment_type);
        $this->assertEquals('midtrans-trx-uuid-999', $transaksi->midtrans_transaction_id);
        $this->assertNotNull($transaksi->dibayar_pada);

        $jadwal->refresh();
        $this->assertEquals('dp_terverifikasi', $jadwal->status_pembayaran);
        $this->assertEquals('terverifikasi', $jadwal->status);
        $this->assertNotNull($jadwal->kode_qr);
        $this->assertNotNull($jadwal->token_manual);
    }

    public function test_midtrans_notification_handles_full_payment_settlement(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => '08123456789',
            'email_tamu' => $this->pelanggan->email,
            'tanggal' => now()->addDays(1)->toDateString(),
            'jam_mulai' => '14:00',
            'jam_selesai' => '15:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $orderId = 'ORDER-MID-FULL';
        $grossAmount = '150000.00';
        $statusCode = '200';

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'full',
            'jumlah' => 150000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => $orderId,
            'referensi_midtrans' => $orderId,
        ]);

        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'capture',
            'payment_type' => 'credit_card',
            'transaction_id' => 'midtrans-trx-cc-888',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(200);

        $transaksi->refresh();
        $this->assertEquals('berhasil', $transaksi->status);

        $jadwal->refresh();
        $this->assertEquals('lunas_online', $jadwal->status_pembayaran);
        $this->assertEquals('lunas', $jadwal->status);
    }

    public function test_midtrans_notification_handles_expire_cancel_deny(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => '08123456789',
            'email_tamu' => $this->pelanggan->email,
            'tanggal' => now()->addDays(1)->toDateString(),
            'jam_mulai' => '16:00',
            'jam_selesai' => '17:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $orderId = 'ORDER-MID-EXPIRE';
        $grossAmount = '150000.00';
        $statusCode = '200';

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'full',
            'jumlah' => 150000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => $orderId,
        ]);

        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'expire',
            'payment_type' => 'echannel',
            'transaction_id' => 'midtrans-trx-exp-111',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(200);

        $transaksi->refresh();
        $this->assertEquals('gagal', $transaksi->status);
        $this->assertEquals('expire', $transaksi->transaction_status);

        $jadwal->refresh();
        $this->assertEquals('dibatalkan', $jadwal->status);
        $this->assertEquals('belum_bayar', $jadwal->status_pembayaran);
    }

    public function test_api_route_and_csrf_exemption_are_active(): void
    {
        $orderId = 'ORDER-MID-API';
        $grossAmount = '50000.00';
        $statusCode = '200';

        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'pending',
            'payment_type' => 'bank_transfer',
        ];

        // Direct POST to /api/midtrans/notification without CSRF token
        $response = $this->post('/api/midtrans/notification', $payload);

        // If CSRF was not exempt, Laravel would return 419 Page Expired
        $this->assertNotEquals(419, $response->getStatusCode());
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Notification handled']);
    }
}
