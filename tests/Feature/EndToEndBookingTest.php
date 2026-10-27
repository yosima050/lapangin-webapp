<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

/**
 * End-to-End Booking Flow Test — LapangIn
 *
 * Skenario UAT lengkap yang mencakup seluruh alur utama aplikasi:
 *   1. Pelanggan registrasi & kontrol akses peran (RBAC)
 *   2. Booking & pembayaran DP via Midtrans Sandbox Webhook
 *   3. Reschedule alur mandiri (H-24 jam) & approval kasir
 *   4. Pembatalan berpenalti (Auto-Penalty Policy)
 *   5. Kasir: scan QR check-in & order walk-in
 *   6. Tiket e-tiket publik & lookup kasir
 *   7. Laporan rekonsiliasi admin & edge-case validasi
 */
class EndToEndBookingTest extends TestCase
{
    use RefreshDatabase;

    protected Pelanggan $pelanggan;
    protected Pelanggan $pelanggan2;
    protected Pelanggan $kasir;
    protected Pelanggan $admin;
    protected Lapangan  $lapangan;
    protected string    $serverKey = 'SB-Mid-server-TEST987654';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', $this->serverKey);
        Config::set('services.midtrans.client_key', 'SB-Mid-client-TEST123456');
        Config::set('services.midtrans.is_production', false);

        $this->pelanggan = Pelanggan::create([
            'nama'          => 'Andi Pelanggan E2E',
            'email'         => 'andi.e2e@lapangin.id',
            'no_hp'         => '081200001111',
            'password_hash' => bcrypt('password123'),
            'role'          => 'pelanggan',
        ]);

        $this->pelanggan2 = Pelanggan::create([
            'nama'          => 'Budi Pelanggan E2E',
            'email'         => 'budi.e2e@lapangin.id',
            'no_hp'         => '081200002222',
            'password_hash' => bcrypt('password123'),
            'role'          => 'pelanggan',
        ]);

        $this->kasir = Pelanggan::create([
            'nama'          => 'Kasir Arena E2E',
            'email'         => 'kasir.e2e@lapangin.id',
            'no_hp'         => '081200003333',
            'password_hash' => bcrypt('password123'),
            'role'          => 'kasir',
        ]);

        $this->admin = Pelanggan::create([
            'nama'          => 'Admin LapangIn',
            'email'         => 'admin.e2e@lapangin.id',
            'no_hp'         => '081200004444',
            'password_hash' => bcrypt('password123'),
            'role'          => 'admin',
        ]);

        $this->lapangan = Lapangan::create([
            'nama'      => 'Lapangan Futsal Vinyl Pro (E2E)',
            'kategori'  => 'Futsal',
            'jam_buka'  => '08:00:00',
            'jam_tutup' => '23:00:00',
            'deskripsi' => 'Lapangan khusus pengujian E2E',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari'  => 'weekday',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga'       => 120000,
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari'  => 'weekend',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga'       => 150000,
        ]);
    }

    // =========================================================================
    // BLOK 1: AUTENTIKASI & KONTROL AKSES (RBAC)
    // =========================================================================

    /** UAT-AUTH-01: Pengguna yang belum login diarahkan ke halaman login */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('pelanggan.dashboard'))->assertRedirect(route('login'));
        $this->get(route('kasir.index'))->assertRedirect();
        $this->get(route('admin.dashboard'))->assertRedirect();
    }

    /** UAT-AUTH-02: Kasir tidak dapat mengakses area admin */
    public function test_kasir_cannot_access_admin_area(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    /** UAT-AUTH-03: Pelanggan tidak dapat mengakses area kasir */
    public function test_pelanggan_cannot_access_kasir_area(): void
    {
        $this->actingAs($this->pelanggan)
            ->get(route('kasir.index'))
            ->assertForbidden();
    }

    // =========================================================================
    // BLOK 2: KATALOG & BOOKING CHECKOUT
    // =========================================================================

    /** UAT-BOOK-01: Pelanggan dapat melihat detail lapangan di katalog */
    public function test_pelanggan_can_view_lapangan_detail(): void
    {
        $response = $this->actingAs($this->pelanggan)
            ->get(route('katalog.show', $this->lapangan->id));

        $response->assertOk();
        $response->assertSee('Lapangan Futsal Vinyl Pro (E2E)');
    }

    /** UAT-BOOK-02: Checkout memvalidasi ketersediaan dan menghasilkan snap token */
    public function test_pelanggan_can_checkout_and_receive_snap_token(): void
    {
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-e2e-snap-token-12345');
        $this->app->instance(PaymentService::class, $mockPaymentService);

        $tanggal = now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->pelanggan)
            ->postJson(route('checkout.pay'), [
                'lapangan_id' => $this->lapangan->id,
                'tanggal'     => $tanggal,
                'jam_mulai'   => '09:00',
                'durasi'      => 1,
                'nama'        => 'Andi Pelanggan E2E',
                'no_hp'       => '081200001111',
                'payType'     => 'dp',
            ]);

        $response->assertOk();
        $response->assertJson([
            'status'     => 'success',
            'snap_token' => 'dummy-e2e-snap-token-12345',
        ]);

        $jadwal = Jadwal::where('lapangan_id', $this->lapangan->id)
            ->whereDate('tanggal', $tanggal)
            ->where('jam_mulai', '09:00:00')
            ->first();

        $this->assertNotNull($jadwal);
        $this->assertEquals('10:00:00', $jadwal->jam_selesai);
    }

    /** UAT-BOOK-03: Webhook Midtrans Sandbox berhasil memverifikasi pembayaran DP */
    public function test_midtrans_webhook_updates_payment_status_to_dp_verified(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addDays(5)->toDateString(),
            'jam_mulai'         => '14:00:00',
            'jam_selesai'       => '15:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'menunggu_pembayaran',
            'status_pembayaran' => 'belum_bayar',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $orderId = 'LAP-' . $jadwal->id . '-DP';

        Transaksi::create([
            'jadwal_id'          => $jadwal->id,
            'order_id'           => $orderId,
            'referensi_midtrans' => $orderId,
            'jumlah'             => 60000,
            'metode'             => 'midtrans',
            'status'             => 'pending',
            'jenis'              => 'dp',
            'snap_token'         => 'test-snap-token',
            'dibayar_pada'       => null,
        ]);

        $statusCode   = '200';
        $grossAmount  = '60000.00';
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $response = $this->postJson(route('midtrans.webhook'), [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'transaction_status' => 'settlement',
            'payment_type'       => 'bank_transfer',
            'signature_key'      => $signatureKey,
        ]);

        $response->assertOk();

        $jadwal->refresh();
        $this->assertEquals('dp_terverifikasi', $jadwal->status_pembayaran);
        $this->assertEquals('terverifikasi', $jadwal->status);
    }

    // =========================================================================
    // BLOK 3: RESCHEDULE JADWAL
    // =========================================================================

    /** UAT-RESC-01: Pelanggan dapat mengajukan reschedule ≥ H-24 jam */
    public function test_pelanggan_can_request_reschedule_with_valid_24h_gap(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addDays(5)->toDateString(),
            'jam_mulai'         => '09:00:00',
            'jam_selesai'       => '10:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $tanggalBaru = now()->addDays(7)->toDateString();

        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.reschedule.store', $jadwal->id), [
                'tanggal_baru'     => $tanggalBaru,
                'jam_mulai_baru'   => '11:00',
                'jam_selesai_baru' => '12:00',
                'alasan_pengajuan' => 'Ada acara keluarga mendadak',
            ]);

        $reschedule = Reschedule::where('jadwal_id', $jadwal->id)->first();
        $this->assertNotNull($reschedule);
        $this->assertEquals($tanggalBaru, $reschedule->tanggal_baru->toDateString());
        $this->assertEquals('diajukan', $reschedule->status);
        $this->assertEquals('Ada acara keluarga mendadak', $reschedule->alasan_pengajuan);

        $response->assertRedirect(route('pelanggan.riwayat'));
    }

    /** UAT-RESC-02: Pelanggan TIDAK bisa reschedule ke slot < 24 jam dari sekarang */
    public function test_pelanggan_cannot_reschedule_to_slot_less_than_24h_from_now(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addDays(5)->toDateString(),
            'jam_mulai'         => '09:00:00',
            'jam_selesai'       => '10:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        // Coba reschedule ke slot hari ini (mepet < 24 jam)
        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.reschedule.store', $jadwal->id), [
                'tanggal_baru'     => now()->toDateString(),
                'jam_mulai_baru'   => '10:00',
                'jam_selesai_baru' => '11:00',
                'alasan_pengajuan' => 'Mau reschedule mepet hari ini',
            ]);

        $response->assertSessionHasErrors(['tanggal_baru']);
        $this->assertDatabaseMissing('reschedules', ['jadwal_id' => $jadwal->id]);
    }

    /** UAT-RESC-03: Kasir dapat menyetujui pengajuan reschedule */
    public function test_kasir_can_approve_reschedule_request(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addDays(5)->toDateString(),
            'jam_mulai'         => '09:00:00',
            'jam_selesai'       => '10:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $tanggalBaru = now()->addDays(8)->toDateString();

        $reschedule = Reschedule::create([
            'jadwal_id'        => $jadwal->id,
            'tanggal_baru'     => $tanggalBaru,
            'jam_mulai_baru'   => '11:00:00',
            'jam_selesai_baru' => '12:00:00',
            'status'           => 'diajukan',
            'alasan_pengajuan' => 'Test reschedule',
            'created_at'       => now(),
        ]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.reschedule.approve', $reschedule->id), [
                'catatan_kasir' => 'Reschedule disetujui',
            ]);

        $this->assertDatabaseHas('reschedules', [
            'id'     => $reschedule->id,
            'status' => 'disetujui',
        ]);

        $jadwal->refresh();
        $this->assertEquals($tanggalBaru, $jadwal->tanggal->toDateString());
        $this->assertEquals('11:00:00', $jadwal->jam_mulai);
    }

    /** UAT-RESC-04: Kasir dapat menolak pengajuan reschedule */
    public function test_kasir_can_reject_reschedule_request(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addDays(5)->toDateString(),
            'jam_mulai'         => '13:00:00',
            'jam_selesai'       => '14:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $reschedule = Reschedule::create([
            'jadwal_id'        => $jadwal->id,
            'tanggal_baru'     => now()->addDays(9)->toDateString(),
            'jam_mulai_baru'   => '14:00:00',
            'jam_selesai_baru' => '15:00:00',
            'status'           => 'diajukan',
            'alasan_pengajuan' => 'Test tolak',
            'created_at'       => now(),
        ]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.reschedule.reject', $reschedule->id), [
                'alasan_kasir' => 'Slot sudah dipesan untuk turnamen',
            ]);

        $this->assertDatabaseHas('reschedules', [
            'id'     => $reschedule->id,
            'status' => 'ditolak',
        ]);

        $jadwal->refresh();
        $this->assertEquals('13:00:00', $jadwal->jam_mulai);
    }

    // =========================================================================
    // BLOK 4: PEMBATALAN & PENALTI OTOMATIS
    // =========================================================================

    /** UAT-CANCEL-01: Pembatalan < 24 jam sebelum kickoff hangus DP (50%) */
    public function test_cancellation_under_24h_applies_dp_penalty(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addHours(12)->toDateString(),
            'jam_mulai'         => now()->addHours(12)->format('H:00:00'),
            'jam_selesai'       => now()->addHours(13)->format('H:00:00'),
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.cancel', $jadwal->id), [
                'alasan' => 'Tidak bisa hadir mendadak',
            ]);

        $jadwal->refresh();
        $this->assertEquals('dibatalkan', $jadwal->status);
        $this->assertEquals(60000, $jadwal->denda_pembatalan);
    }

    /** UAT-CANCEL-02: Pembatalan ≥ 24 jam hanya penalti 10% administrasi */
    public function test_cancellation_over_24h_applies_10_percent_admin_fee(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->addDays(5)->toDateString(),
            'jam_mulai'         => '09:00:00',
            'jam_selesai'       => '10:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.cancel', $jadwal->id), [
                'alasan' => 'Rencana berubah',
            ]);

        $jadwal->refresh();
        $this->assertEquals('dibatalkan', $jadwal->status);
        $this->assertEquals(12000, $jadwal->denda_pembatalan);
    }

    // =========================================================================
    // BLOK 5: KASIR — CHECK-IN & WALK-IN ORDER
    // =========================================================================

    /** UAT-KASIR-01: Kasir dapat melakukan check-in dan pelunasan onsite */
    public function test_kasir_can_checkin_valid_ticket(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->toDateString(),
            'jam_mulai'         => now()->subMinutes(10)->format('H:00:00'),
            'jam_selesai'       => now()->addHours(1)->format('H:00:00'),
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'token_manual'      => 'TKN777',
            'kode_qr'           => 'LPNG-QR-TKN777',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        // Simulasikan DP awal 60.000 sudah tercatat berhasil
        Transaksi::create([
            'jadwal_id'          => $jadwal->id,
            'order_id'           => 'LAP-' . $jadwal->id . '-DP-INIT',
            'referensi_midtrans' => 'LAP-' . $jadwal->id . '-DP-INIT',
            'jumlah'             => 60000,
            'metode'             => 'midtrans',
            'status'             => 'berhasil',
            'jenis'              => 'dp',
            'dibayar_pada'       => now(),
        ]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.checkin', $jadwal->id), [
                'metode'  => 'tunai',
                'jumlah'  => 60000,
                'catatan' => 'Pelunasan tunai kasir meja 1',
            ]);

        $response->assertRedirect();
        $jadwal->refresh();
        $this->assertNotNull($jadwal->checked_in_at);
        $this->assertEquals('lunas_kasir', $jadwal->status_pembayaran);
    }

    /** UAT-KASIR-02: Kasir TIDAK BISA check-in tiket yang sudah dibatalkan */
    public function test_kasir_cannot_checkin_cancelled_ticket(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan->id,
            'tanggal'           => now()->toDateString(),
            'jam_mulai'         => '10:00:00',
            'jam_selesai'       => '11:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'dibatalkan',
            'status_pembayaran' => 'dp_terverifikasi',
            'token_manual'      => 'CNCL99',
            'kode_qr'           => 'LPNG-QR-CNCL99',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 60000,
            'created_at'        => now(),
        ]);

        $response = $this->actingAs($this->kasir)
            ->postJson(route('kasir.checkin', $jadwal->id), [
                'metode' => 'tunai',
                'jumlah' => 60000,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error'   => 'cancelled',
        ]);
    }

    /** UAT-KASIR-03: Kasir dapat membuat walk-in order untuk slot kosong */
    public function test_kasir_can_create_walkin_order_for_empty_slot(): void
    {
        $tanggal = now()->addDays(2)->toDateString();

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.walkin.store'), [
                'nama_tamu'   => 'Tamu Walkin Baru',
                'no_wa_tamu'  => '081288889999',
                'lapangan_id' => $this->lapangan->id,
                'tanggal'     => $tanggal,
                'jam_mulai'   => '14:00',
                'durasi'      => 1,
                'payType'     => 'full',
            ]);

        $jadwal = Jadwal::where('lapangan_id', $this->lapangan->id)
            ->whereDate('tanggal', $tanggal)
            ->where('jam_mulai', '14:00:00')
            ->first();

        $this->assertNotNull($jadwal);
        $this->assertEquals('terverifikasi', $jadwal->status);
        $response->assertRedirect(route('kasir.jadwal', ['tanggal' => $tanggal, 'lapangan_id' => $this->lapangan->id]));
    }

    // =========================================================================
    // BLOK 6: E-TIKET & LOOKUP TIKET
    // =========================================================================

    /** UAT-TIKET-01: Kasir dan publik dapat me-lookup tiket via kode token */
    public function test_ticket_lookup_by_token(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'nama_tamu'         => 'Budi Tamu',
            'no_wa_tamu'        => '081277778888',
            'tanggal'           => now()->addDays(3)->toDateString(),
            'jam_mulai'         => '08:00:00',
            'jam_selesai'       => '09:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'terverifikasi',
            'status_pembayaran' => 'dp_terverifikasi',
            'token_manual'      => 'VAL888',
            'kode_qr'           => 'LPNG-QR-VAL888',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 0,
            'created_at'        => now(),
        ]);

        $response = $this->actingAs($this->kasir)
            ->get(route('kasir.ticket.detail', 'VAL888'));

        $response->assertOk();
        $response->assertSee('VAL888');
    }

    // =========================================================================
    // BLOK 7: ADMIN — LAPORAN REKONSILIASI
    // =========================================================================

    /** UAT-ADMIN-01: Admin dapat mengakses halaman laporan rekonsiliasi dan export */
    public function test_admin_can_access_rekonsiliasi_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.laporan.index'));

        $response->assertOk();
    }

    /** UAT-ADMIN-02: Admin dapat melihat statistik di dashboard utama */
    public function test_admin_can_view_dashboard_with_stats(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
    }

    // =========================================================================
    // BLOK 8: EDGE CASES & KONSISTENSI STATUS PEMBATALAN
    // =========================================================================

    /** UAT-SEC-01: Slot yang berstatus dibatalkan tidak memblokir booking baru */
    public function test_cancelled_slot_is_ignored_and_allows_new_booking(): void
    {
        $today = now()->addDays(3)->toDateString();

        Jadwal::create([
            'lapangan_id'       => $this->lapangan->id,
            'pelanggan_id'      => $this->pelanggan2->id,
            'tanggal'           => $today,
            'jam_mulai'         => '10:00:00',
            'jam_selesai'       => '11:00:00',
            'harga_disepakati'  => 120000,
            'biaya_layanan'     => 0,
            'status'            => 'dibatalkan',
            'status_pembayaran' => 'dp_terverifikasi',
            'sewa_rompi'        => 0,
            'sewa_bola'         => 0,
            'denda_pembatalan'  => 60000,
            'created_at'        => now(),
        ]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.walkin.store'), [
                'nama_tamu'   => 'Walkin Penimpa Slot Batal',
                'no_wa_tamu'  => '081233445566',
                'lapangan_id' => $this->lapangan->id,
                'tanggal'     => $today,
                'jam_mulai'   => '10:00',
                'durasi'      => 1,
                'payType'     => 'full',
            ]);

        $response->assertRedirect();

        $jadwal = Jadwal::where('nama_tamu', 'Walkin Penimpa Slot Batal')
            ->whereDate('tanggal', $today)
            ->where('jam_mulai', '10:00:00')
            ->first();

        $this->assertNotNull($jadwal);
        $this->assertEquals('terverifikasi', $jadwal->status);
    }
}
