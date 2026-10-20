<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_out_of_hours_interval(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Test ' . uniqid(),
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Deskripsi lapangan test',
        ]);

        $pelanggan = Pelanggan::factory()->create(['role' => 'pelanggan', 'email_verified_at' => now()]);

        // Request starting before jam_buka
        $responseBefore = $this->actingAs($pelanggan)->get(route('checkout.index', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '07:00',
            'durasi' => 1,
        ]));
        $responseBefore->assertStatus(422);

        // Request ending after jam_tutup
        $responseAfter = $this->actingAs($pelanggan)->get(route('checkout.index', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '21:00',
            'durasi' => 2,
        ]));
        $responseAfter->assertStatus(422);
    }

    public function test_checkout_rejects_occupied_slots(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Test ' . uniqid(),
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Deskripsi lapangan test',
        ]);

        $pelanggan = Pelanggan::factory()->create(['role' => 'pelanggan', 'email_verified_at' => now()]);

        // Existing booking at 10:00 - 12:00
        Jadwal::create([
            'lapangan_id' => $lapangan->id,
            'pelanggan_id' => $pelanggan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'status' => 'terverifikasi',
            'status_pembayaran' => 'lunas_kasir',
        ]);

        // Overlapping request at 11:00 - 13:00
        $response = $this->actingAs($pelanggan)->get(route('checkout.index', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '11:00',
            'durasi' => 2,
        ]));
        $response->assertStatus(422);
    }

    public function test_checkout_calculates_total_harga_across_hourly_rates(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Test Rates ' . uniqid(),
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '23:00',
            'deskripsi' => 'Deskripsi lapangan test',
        ]);

        $pelanggan = Pelanggan::factory()->create(['role' => 'pelanggan', 'email_verified_at' => now()]);

        // Weekday rate 1: 08:00 - 15:00 = 100,000
        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00',
            'jam_selesai' => '15:00',
            'harga' => 100000,
        ]);

        // Weekday rate 2: 15:00 - 23:00 = 150,000
        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '15:00',
            'jam_selesai' => '23:00',
            'harga' => 150000,
        ]);

        // Pick next weekday
        $testDate = Carbon::now()->next(Carbon::WEDNESDAY)->toDateString();

        // 2 hours spanning 14:00 to 16:00 (1 hour @ 100k + 1 hour @ 150k = 250k)
        $response = $this->actingAs($pelanggan)->get(route('checkout.index', [
            'lapangan_id' => $lapangan->id,
            'tanggal' => $testDate,
            'jam_mulai' => '14:00',
            'durasi' => 2,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rp250.000');
    }

    public function test_katalog_harga_termurah_excludes_unrelated_tanggal_khusus(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Test ' . uniqid(),
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Deskripsi lapangan test',
        ]);

        // Regular weekday rate: 150,000
        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 150000,
        ]);

        // Unrelated special date (in future) with cheap promo rate: 50,000
        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'tanggal_khusus' => '2026-12-25',
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 50000,
        ]);

        $testDate = Carbon::now()->next(Carbon::TUESDAY)->toDateString();

        $response = $this->get(route('katalog.index', ['tanggal' => $testDate]));
        $response->assertStatus(200);

        // On this regular weekday, promo 50,000 for 2026-12-25 should NOT be used as harga_termurah
        $responseJson = $this->getJson(route('katalog.index', ['tanggal' => $testDate]));
        $responseJson->assertStatus(200);
        $item = collect($responseJson->json('data'))->firstWhere('lapangan_id', $lapangan->id);
        $this->assertEquals(150000, $item['harga_termurah']);
    }

    public function test_riwayat_controller_resolves_pelanggan_riwayat_view(): void
    {
        $pelanggan = Pelanggan::factory()->create(['role' => 'pelanggan', 'email_verified_at' => now()]);

        $response = $this->actingAs($pelanggan)->get(route('pelanggan.riwayat'));
        $response->assertStatus(200);

        $responseBooking = $this->actingAs($pelanggan)->get(route('pelanggan.booking.index'));
        $responseBooking->assertStatus(200);
    }

    public function test_login_view_has_secure_whatsapp_link(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('rel="noopener noreferrer"', false);
        $response->assertSee(config('app.support_whatsapp', '6281234567890'));
    }

    public function test_katalog_harga_termurah_prioritizes_special_date_over_regular_rates(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Special Promo ' . uniqid(),
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Deskripsi lapangan test',
        ]);

        // Regular weekday rate: 100,000 for full operating hours
        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 100000,
        ]);

        $specialDate = '2026-11-20';

        // Special date rate: 180,000 replaces regular rate for full operating hours
        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'tanggal_khusus' => $specialDate,
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 180000,
        ]);

        $responseJson = $this->getJson(route('katalog.index', ['tanggal' => $specialDate]));
        $responseJson->assertStatus(200);

        $item = collect($responseJson->json('data'))->firstWhere('lapangan_id', $lapangan->id);
        // Special-date rate replaced regular rate, so minimum should be 180,000, NOT 100,000
        $this->assertEquals(180000, $item['harga_termurah']);
    }

    public function test_database_seeder_preserves_email_verified_at_in_production(): void
    {
        $existingAdmin = Pelanggan::create([
            'email' => 'admin@lapangin.com',
            'nama' => 'Existing Admin',
            'no_hp' => '081234567890',
            'password_hash' => bcrypt('admin1'),
            'role' => 'admin',
            'email_verified_at' => null,
            'created_at' => now(),
        ]);

        $this->app->detectEnvironment(fn () => 'production');
        $this->assertTrue(app()->isProduction());

        (new \Database\Seeders\DatabaseSeeder)->run();

        $existingAdmin->refresh();
        $this->assertNull($existingAdmin->email_verified_at);
    }

    public function test_katalog_validates_tanggal_format_and_rejects_malformed_input(): void
    {
        $response = $this->getJson(route('katalog.index', ['tanggal' => 'not-a-valid-date']));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal']);

        $responseValid = $this->get(route('katalog.index', ['tanggal' => now()->toDateString()]));
        $responseValid->assertStatus(200);

        $responseOmitted = $this->get(route('katalog.index'));
        $responseOmitted->assertStatus(200);
    }
}
