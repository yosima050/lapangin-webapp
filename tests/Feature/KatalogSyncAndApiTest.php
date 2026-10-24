<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Lapangan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogSyncAndApiTest extends TestCase
{
    use RefreshDatabase;

    private Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lapangan = Lapangan::create([
            'nama' => 'Grand Futsal Arena',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Lapangan futsal rumput sintetis standar FIFA.',
            'foto' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 125000,
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekend',
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 175000,
        ]);
    }

    public function test_katalog_page_displays_synchronized_data_from_database(): void
    {
        $response = $this->get(route('katalog.index', [
            'tanggal' => '2026-10-23',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Grand Futsal Arena');
        $response->assertSee('Futsal');
        $response->assertSee('125.000');
        $response->assertSee($this->lapangan->foto_url, false);
    }

    public function test_katalog_supports_json_api_content_negotiation(): void
    {
        $response = $this->getJson(route('katalog.index'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'lapangan_id',
                    'lapangan_nama',
                    'kategori',
                    'foto_url',
                    'deskripsi',
                    'harga_weekday',
                    'harga_weekend',
                    'harga_termurah',
                    'jam_operasional',
                    'slots',
                ]
            ],
            'kategori_list',
        ]);

        $this->assertEquals('Grand Futsal Arena', $response->json('data.0.lapangan_nama'));
        $this->assertEquals(125000, $response->json('data.0.harga_weekday'));
    }

    public function test_katalog_detail_displays_synced_pricing_and_photo(): void
    {
        $response = $this->get(route('katalog.show', $this->lapangan->id));

        $response->assertStatus(200);
        $response->assertSee('Grand Futsal Arena');
        $response->assertSee('125.000');
        $response->assertSee('175.000');
        $response->assertSee($this->lapangan->foto_url, false);
        $response->assertSee('/checkout?lapangan_id=' . $this->lapangan->id, false);
    }

    public function test_authenticated_users_can_access_checkout_without_403(): void
    {
        $admin = \App\Models\Pelanggan::create([
            'nama' => 'Admin Test Checkout',
            'email' => 'admin.checkout@lapangin.com',
            'no_hp' => '081299991111',
            'password_hash' => bcrypt('admin1'),
            'role' => 'admin',
        ]);

        $pelanggan = \App\Models\Pelanggan::create([
            'nama' => 'Pelanggan Test Checkout',
            'email' => 'pelanggan.checkout@lapangin.com',
            'no_hp' => '081299992222',
            'password_hash' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);

        $checkoutParams = [
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '09:00',
            'durasi' => 1,
        ];

        // Guest is redirected to login
        $guestResponse = $this->get(route('checkout.index', $checkoutParams));
        $guestResponse->assertRedirect(route('login'));

        // Admin can access checkout without 403 Forbidden
        $adminResponse = $this->actingAs($admin)->get(route('checkout.index', $checkoutParams));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Checkout Booking');

        // Pelanggan can access checkout without 403 Forbidden
        $pelangganResponse = $this->actingAs($pelanggan)->get(route('checkout.index', $checkoutParams));
        $pelangganResponse->assertStatus(200);
        $pelangganResponse->assertSee('Checkout Booking');
    }
}
