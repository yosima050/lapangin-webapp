<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class MidtransSnapAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $serverKey = 'SB-Mid-server-TEST987654';

    private Lapangan $lapangan;

    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', $this->serverKey);
        Config::set('services.midtrans.client_key', 'SB-Mid-client-TEST123456');
        Config::set('services.midtrans.is_production', false);
        Config::set('services.midtrans.is_sanitized', true);
        Config::set('services.midtrans.is_3ds', true);

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Futsal A',
            'kategori' => 'Futsal',
            'jam_buka' => '07:00',
            'jam_tutup' => '23:00',
            'deskripsi' => 'Lapangan Futsal Standar Nasional',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '07:00',
            'jam_selesai' => '23:00',
            'harga' => 150000,
        ]);

        $this->pelanggan = Pelanggan::factory()->create([
            'nama' => 'Yosima Customer',
            'email' => 'yosima.customer@lapangin.id',
            'no_hp' => '081234567899',
            'role' => 'pelanggan',
            'email_verified_at' => now(),
        ]);
    }

    public function test_payment_service_initializes_midtrans_config(): void
    {
        $service = new PaymentService;
        $service->initMidtrans();

        $this->assertEquals($this->serverKey, \Midtrans\Config::$serverKey);
        $this->assertFalse(\Midtrans\Config::$isProduction);
        $this->assertTrue(\Midtrans\Config::$isSanitized);
        $this->assertTrue(\Midtrans\Config::$is3ds);
    }

    public function test_payment_service_uses_cached_snap_token_if_available(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => $this->pelanggan->no_hp,
            'email_tamu' => $this->pelanggan->email,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 75000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => 'LPNG-TEST-CACHED',
            'snap_token' => 'cached-snap-token-12345',
        ]);

        $service = new PaymentService;
        $token = $service->createSnapToken($transaksi, $this->pelanggan);

        $this->assertEquals('cached-snap-token-12345', $token);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $payload = [
            'order_id' => 'LPNG-TEST-INV',
            'status_code' => '200',
            'gross_amount' => '75000.00',
            'signature_key' => 'invalid-sha512-signature',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature key']);
    }

    public function test_midtrans_webhook_handles_settlement_and_updates_status_and_generates_qr(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => $this->pelanggan->no_hp,
            'email_tamu' => $this->pelanggan->email,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $orderId = 'LPNG-SETTLE-001';
        $grossAmount = '75000.00';
        $statusCode = '200';

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 75000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => $orderId,
            'referensi_midtrans' => $orderId,
        ]);

        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-mid-999',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
        ]);

        $transaksi->refresh();
        $this->assertEquals('berhasil', $transaksi->status);
        $this->assertNotNull($transaksi->dibayar_pada);
        $this->assertEquals('settlement', $transaksi->transaction_status);

        $jadwal->refresh();
        $this->assertEquals('dp_terverifikasi', $jadwal->status_pembayaran);
        $this->assertEquals('terverifikasi', $jadwal->status);
        $this->assertNotNull($jadwal->kode_qr);
        $this->assertStringStartsWith('LPNG-QR-', $jadwal->kode_qr);
        $this->assertNotNull($jadwal->token_manual);
        $this->assertEquals(6, strlen($jadwal->token_manual));
    }

    public function test_midtrans_webhook_handles_full_payment_settlement(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => $this->pelanggan->no_hp,
            'email_tamu' => $this->pelanggan->email,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $orderId = 'LPNG-SETTLE-FULL-001';
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

        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'trx-mid-full-999',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);

        $transaksi->refresh();
        $this->assertEquals('berhasil', $transaksi->status);

        $jadwal->refresh();
        $this->assertEquals('lunas_online', $jadwal->status_pembayaran);
        $this->assertEquals('terverifikasi', $jadwal->status);
        $this->assertNotNull($jadwal->kode_qr);
        $this->assertNotNull($jadwal->token_manual);
    }

    public function test_midtrans_webhook_handles_pending_status(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $orderId = 'LPNG-PENDING-001';
        $grossAmount = '75000.00';
        $statusCode = '201';

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 75000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => $orderId,
        ]);

        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'pending',
            'payment_type' => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
        ]);

        $transaksi->refresh();
        $this->assertEquals('pending', $transaksi->status);
    }

    public function test_midtrans_webhook_handles_expire_cancel_deny_status(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '15:00',
            'jam_selesai' => '16:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $orderId = 'LPNG-EXPIRE-001';
        $grossAmount = '75000.00';
        $statusCode = '200';

        $transaksi = Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 75000,
            'metode' => 'online',
            'status' => 'pending',
            'order_id' => $orderId,
        ]);

        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'payment_type' => 'echannel',
        ];

        $response = $this->postJson(route('midtrans.webhook'), $payload);

        $response->assertStatus(200);

        $transaksi->refresh();
        $this->assertEquals('gagal', $transaksi->status);

        $jadwal->refresh();
        $this->assertEquals('belum_bayar', $jadwal->status_pembayaran);
        $this->assertEquals('dibatalkan', $jadwal->status);
    }

    public function test_webhook_bypasses_csrf_protection(): void
    {
        $orderId = 'LPNG-CSRF-BYPASS';
        $grossAmount = '75000.00';
        $statusCode = '200';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        // Direct POST to /midtrans/webhook without CSRF token
        $response = $this->post('/midtrans/webhook', $payload);

        $this->assertNotEquals(419, $response->getStatusCode());
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
        ]);
    }

    public function test_checkout_pay_endpoint_creates_order_and_returns_snap_token(): void
    {
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-mock-snap-token-12345');

        $this->app->instance(PaymentService::class, $mockPaymentService);

        $payload = [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '08:00',
            'durasi' => 2,
        ];

        $response = $this->actingAs($this->pelanggan)->postJson(route('checkout.pay'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'snap_token' => 'dummy-mock-snap-token-12345',
        ]);

        $this->assertDatabaseHas('jadwals', [
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'status' => 'pending',
            'status_pembayaran' => 'belum_bayar',
        ]);

        $this->assertDatabaseHas('transaksis', [
            'jenis' => 'dp',
            'status' => 'pending',
            'metode' => 'online',
        ]);
    }

    public function test_checkout_view_displays_midtrans_info_card_and_no_static_placeholders(): void
    {
        $response = $this->actingAs($this->pelanggan)->get(route('checkout.index', [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '08:00',
            'durasi' => 2,
        ]));

        $response->assertStatus(200);

        // Pastikan info card Midtrans Snap tampil
        $response->assertSee('Metode Pembayaran Tersedia via Midtrans Snap');
        $response->assertSee('id="pay-button"', false);
        $response->assertSee('Bayar Sekarang');
        $response->assertSee('Bayar DP (50%)');
        $response->assertSee('Bayar Lunas (100%)');
        $response->assertSee('https://app.sandbox.midtrans.com/snap/snap.js', false);

        // Pastikan placeholder statis sudah dihapus
        $response->assertDontSee('Pembayaran QRIS belum tersedia');
        $response->assertDontSee('Menunggu Integrasi Pembayaran');
        $response->assertDontSee('Segera hadir');
    }

    public function test_checkout_pay_endpoint_creates_order_with_full_payment_and_returns_snap_token(): void
    {
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-mock-snap-token-full-67890');

        $this->app->instance(PaymentService::class, $mockPaymentService);

        $payload = [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '08:00',
            'durasi' => 2,
            'tipe_bayar' => 'full',
        ];

        $response = $this->actingAs($this->pelanggan)->postJson(route('checkout.pay'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'snap_token' => 'dummy-mock-snap-token-full-67890',
        ]);

        $this->assertDatabaseHas('transaksis', [
            'jenis' => 'full',
            'jumlah' => 300000,
            'status' => 'pending',
            'metode' => 'online',
        ]);
    }

    public function test_checkout_pay_endpoint_handles_direct_jadwal_id(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '18:00',
            'jam_selesai' => '20:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'belum_bayar',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('snap-token-direct-jadwal-999');

        $this->app->instance(PaymentService::class, $mockPaymentService);

        $response = $this->actingAs($this->pelanggan)->postJson(route('checkout.pay'), [
            'jadwal_id' => $jadwal->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'snap_token' => 'snap-token-direct-jadwal-999',
        ]);

        $this->assertDatabaseHas('transaksis', [
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 150000,
            'status' => 'pending',
            'metode' => 'online',
        ]);
    }
}
