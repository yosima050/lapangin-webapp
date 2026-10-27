<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Services\GuestAccountProvisioner;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GuestCheckoutAndAutoClaimTest extends TestCase
{
    use RefreshDatabase;

    private Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Futsal Bima',
            'kategori' => 'Futsal',
            'jam_buka' => '07:00',
            'jam_tutup' => '23:00',
            'deskripsi' => 'Lapangan Futsal Standar Sintetis',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '07:00',
            'jam_selesai' => '23:00',
            'harga' => 150000,
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekend',
            'jam_mulai' => '07:00',
            'jam_selesai' => '23:00',
            'harga' => 180000,
        ]);
    }

    /**
     * AC 1: Pengguna tamu (Walk-In) dapat menyelesaikan proses pembayaran Midtrans tanpa registrasi manual
     * karena sistem otomatis membuatkan record pelanggan berbasis nomor WhatsApp.
     */
    public function test_guest_can_checkout_and_auto_provision_shadow_account(): void
    {
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-snap-token-guest-123');
        $this->app->instance(PaymentService::class, $mockPaymentService);

        $tanggal = Carbon::tomorrow()->toDateString();

        $response = $this->postJson(route('checkout.pay'), [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '10:00',
            'durasi' => 2,
            'tipe_bayar' => 'dp',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567891',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'snap_token' => 'dummy-snap-token-guest-123',
        ]);

        // Verifikasi record pelanggan (shadow account) otomatis dibuat
        $this->assertDatabaseHas('pelanggans', [
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567891',
            'role' => 'pelanggan',
        ]);

        $pelanggan = Pelanggan::where('no_hp', '081234567891')->first();
        $this->assertNotNull($pelanggan);

        // Verifikasi transaksi dan jadwal terikat ke pelanggan_id yang valid
        $this->assertDatabaseHas('jadwals', [
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $pelanggan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '10:00:00',
        ]);

        $this->assertDatabaseHas('transaksis', [
            'status' => 'pending',
            'metode' => 'online',
        ]);
    }

    /**
     * AC 2: Sistem tidak membuat duplikasi data pelanggan jika nomor WhatsApp yang sama
     * digunakan kembali untuk pemesanan berikutnya (firstOrCreate by no_hp).
     */
    public function test_system_does_not_duplicate_guest_record_on_repeated_orders(): void
    {
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('createSnapToken')
            ->twice()
            ->andReturn('dummy-snap-token-repeated');
        $this->app->instance(PaymentService::class, $mockPaymentService);

        $tanggal = Carbon::tomorrow()->toDateString();

        // Pesanan Pertama
        $this->postJson(route('checkout.pay'), [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '13:00',
            'durasi' => 1,
            'tipe_bayar' => 'full',
            'nama' => 'Andi Walkin',
            'no_hp' => '089876543210',
        ])->assertOk();

        $initialCount = Pelanggan::where('no_hp', '089876543210')->count();
        $this->assertEquals(1, $initialCount);

        // Pesanan Kedua dengan nomor WhatsApp sama (meskipun diawali +62 atau format variasi)
        $this->postJson(route('checkout.pay'), [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '15:00',
            'durasi' => 1,
            'tipe_bayar' => 'full',
            'nama' => 'Andi Walkin',
            'no_hp' => '6289876543210',
        ])->assertOk();

        // Jumlah record di tabel pelanggans untuk nomor ini harus tetap 1 (tidak duplikat)
        $finalCount = Pelanggan::where('no_hp', '089876543210')->count();
        $this->assertEquals(1, $finalCount);

        $pelanggan = Pelanggan::where('no_hp', '089876543210')->first();

        // Kedua jadwal harus terhubung ke pelanggan_id yang sama
        $this->assertEquals(2, Jadwal::where('pelanggan_id', $pelanggan->id)->count());
    }

    /**
     * AC 3: Saat pengguna mendaftar secara resmi dengan nomor WhatsApp tersebut,
     * seluruh riwayat pemesanan lama langsung terhubung dan dapat diakses dari halaman Riwayat Booking.
     */
    public function test_official_registration_auto_claims_past_guest_orders(): void
    {
        $provisioner = app(GuestAccountProvisioner::class);

        // 1. Buat shadow account dan 2 pesanan tamu masa lalu
        $guest = $provisioner->getOrCreateGuestAccount('Citra Guest', '085512345678');

        $jadwal1 = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $guest->id,
            'nama_tamu' => 'Citra Guest',
            'no_wa_tamu' => '085512345678',
            'tanggal' => Carbon::yesterday()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'lunas',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now()->subDay(),
        ]);

        $jadwal2 = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $guest->id,
            'nama_tamu' => 'Citra Guest',
            'no_wa_tamu' => '085512345678',
            'tanggal' => Carbon::today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // 2. Pengguna mendaftar secara resmi melalui alur /register
        $response = $this->post(route('register'), [
            'name' => 'Citra Lestari Official',
            'email' => 'citra.lestari@example.com',
            'no_hp' => '085512345678',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        // 3. Verifikasi akun resmi baru terdaftar
        $officialUser = Pelanggan::where('email', 'citra.lestari@example.com')->first();
        $this->assertNotNull($officialUser);

        // 4. Verifikasi bahwa kedua pesanan lama kini otomatis terhubung ke officialUser
        $this->assertEquals($officialUser->id, $jadwal1->fresh()->pelanggan_id);
        $this->assertEquals($officialUser->id, $jadwal2->fresh()->pelanggan_id);

        // 5. Verifikasi pesanan dapat diakses di halaman Riwayat Booking oleh pengguna baru
        $this->actingAs($officialUser);
        $riwayatResponse = $this->get(route('pelanggan.booking.index'));
        $riwayatResponse->assertOk();
        $riwayatResponse->assertSee('Lapangan Futsal Bima');
    }

    /**
     * AC 3 (Lanjutan): Auto-claim pesanan lama ketika menyelesaikan onboarding Google SSO.
     */
    public function test_google_sso_completion_auto_claims_past_guest_orders(): void
    {
        $provisioner = app(GuestAccountProvisioner::class);

        // Tamu pernah pesan walk-in menggunakan nomor 087788990011
        $guest = $provisioner->getOrCreateGuestAccount('Dimas Tamu', '087788990011');

        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $guest->id,
            'nama_tamu' => 'Dimas Tamu',
            'no_wa_tamu' => '087788990011',
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'jam_mulai' => '16:00:00',
            'jam_selesai' => '18:00:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'lunas',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // Simulasikan session Google SSO onboarding
        $sessionData = [
            'nama' => 'Dimas Google User',
            'email' => 'dimas.google@gmail.com',
            'avatar' => 'https://lh3.googleusercontent.com/a/dummy',
        ];

        $response = $this->withSession(['google_complete_user' => $sessionData])
            ->post(route('auth.google.storeComplete'), [
                'nama' => 'Dimas Google User',
                'no_hp' => '087788990011',
            ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        // Verifikasi akun Google berhasil dibuat
        $googleUser = Pelanggan::where('email', 'dimas.google@gmail.com')->first();
        $this->assertNotNull($googleUser);

        // Verifikasi jadwal lama otomatis ter-klaim ke akun Google
        $this->assertEquals($googleUser->id, $jadwal->fresh()->pelanggan_id);
    }
}

