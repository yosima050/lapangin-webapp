<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminLapanganCrudTest extends TestCase
{
    use RefreshDatabase;

    private Pelanggan $admin;
    private Pelanggan $pelangganBiasa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pelanggan::create([
            'nama' => 'Admin Test',
            'email' => 'admin.test@lapangin.com',
            'no_hp' => '081299990001',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->pelangganBiasa = Pelanggan::create([
            'nama' => 'User Biasa',
            'email' => 'user.biasa@lapangin.com',
            'no_hp' => '081299990002',
            'password_hash' => bcrypt('password123'),
            'role' => 'pelanggan',
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $responseUnauth = $this->get(route('admin.lapangan.index'));
        $responseUnauth->assertRedirect(route('login'));

        $responsePelanggan = $this->actingAs($this->pelangganBiasa)
            ->get(route('admin.lapangan.index'));
        $responsePelanggan->assertStatus(403);
    }

    public function test_admin_can_view_lapangan_index_and_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.lapangan.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Lapangan Olahraga');

        $responseCreate = $this->actingAs($this->admin)->get(route('admin.lapangan.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Tambah Lapangan Baru');
    }

    public function test_admin_can_store_new_lapangan_with_prices(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('lapangan_baru.jpg', 600, 400);

        $payload = [
            'nama' => 'Arena Futsal Merdeka',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '23:00',
            'deskripsi' => 'Lapangan rumput sintetis premium berstandar internasional.',
            'foto' => $file,
            'harga_weekday' => 150000,
            'harga_weekend' => 180000,
            'tanggal_khusus' => '2026-12-25',
            'harga_tanggal_merah' => 200000,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.lapangan.store'), $payload);

        $response->assertRedirect(route('admin.lapangan.index'));
        $response->assertSessionHas('success');

        // Verifikasi lapangan tersimpan di database
        $this->assertDatabaseHas('lapangans', [
            'nama' => 'Arena Futsal Merdeka',
            'kategori' => 'Futsal',
            'deskripsi' => 'Lapangan rumput sintetis premium berstandar internasional.',
        ]);

        $lapangan = Lapangan::where('nama', 'Arena Futsal Merdeka')->first();
        $this->assertNotNull($lapangan);
        $this->assertNotNull($lapangan->foto);
        Storage::disk('public')->assertExists($lapangan->foto);

        // Verifikasi harga_masters tersimpan di database
        $this->assertDatabaseHas('harga_masters', [
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'tanggal_khusus' => null,
            'harga' => 150000,
        ]);

        $this->assertDatabaseHas('harga_masters', [
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekend',
            'tanggal_khusus' => null,
            'harga' => 180000,
        ]);

        $this->assertDatabaseHas('harga_masters', [
            'lapangan_id' => $lapangan->id,
            'harga' => 200000,
        ]);
        $this->assertNotNull(HargaMaster::where('lapangan_id', $lapangan->id)->whereNotNull('tanggal_khusus')->first());
    }

    public function test_price_validation_fails_when_price_is_negative(): void
    {
        $payloadNegativePrice = [
            'nama' => 'Lapangan Minus',
            'kategori' => 'Badminton',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'harga_weekday' => -50000,
            'harga_weekend' => -10000,
            'tanggal_khusus' => '2026-12-25',
            'harga_tanggal_merah' => -25000,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.lapangan.store'), $payloadNegativePrice);

        $response->assertSessionHasErrors(['harga_weekday', 'harga_weekend', 'harga_tanggal_merah']);
        $this->assertDatabaseMissing('lapangans', [
            'nama' => 'Lapangan Minus',
        ]);
    }

    public function test_admin_can_update_lapangan_and_prices(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Lama',
            'kategori' => 'Basket',
            'jam_buka' => '09:00',
            'jam_tutup' => '21:00',
            'deskripsi' => 'Deskripsi lama',
        ]);

        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '09:00',
            'jam_selesai' => '21:00',
            'harga' => 100000,
        ]);

        $updatePayload = [
            'nama' => 'Lapangan Basket Pro',
            'kategori' => 'Basket',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
            'deskripsi' => 'Deskripsi baru yang diperbarui',
            'harga_weekday' => 125000,
            'harga_weekend' => 160000,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.lapangan.update', $lapangan->id), $updatePayload);

        $response->assertRedirect(route('admin.lapangan.index'));
        $this->assertDatabaseHas('lapangans', [
            'id' => $lapangan->id,
            'nama' => 'Lapangan Basket Pro',
            'deskripsi' => 'Deskripsi baru yang diperbarui',
        ]);

        $this->assertDatabaseHas('harga_masters', [
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'harga' => 125000,
        ]);
    }

    public function test_admin_can_delete_lapangan(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Akan Dihapus',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00',
            'jam_tutup' => '22:00',
        ]);

        HargaMaster::create([
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00',
            'jam_selesai' => '22:00',
            'harga' => 100000,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.lapangan.destroy', $lapangan->id));

        $response->assertRedirect(route('admin.lapangan.index'));
        $this->assertDatabaseMissing('lapangans', ['id' => $lapangan->id]);
        $this->assertDatabaseMissing('harga_masters', ['lapangan_id' => $lapangan->id]);
    }

    public function test_harga_master_crud_and_negative_price_validation(): void
    {
        $lapangan = Lapangan::create([
            'nama' => 'Lapangan Badminton C',
            'kategori' => 'Badminton',
            'jam_buka' => '07:00',
            'jam_tutup' => '23:00',
        ]);

        // Negative price rejected
        $negativeResponse = $this->actingAs($this->admin)
            ->post(route('admin.harga.store'), [
                'lapangan_id' => $lapangan->id,
                'jenis_hari' => 'weekday',
                'jam_mulai' => '07:00',
                'jam_selesai' => '23:00',
                'harga' => -20000,
            ]);
        $negativeResponse->assertSessionHasErrors(['harga']);

        // Valid price accepted
        $validResponse = $this->actingAs($this->admin)
            ->post(route('admin.harga.store'), [
                'lapangan_id' => $lapangan->id,
                'jenis_hari' => 'weekday',
                'jam_mulai' => '07:00',
                'jam_selesai' => '23:00',
                'harga' => 85000,
            ]);
        $validResponse->assertRedirect(route('admin.harga.index'));

        $this->assertDatabaseHas('harga_masters', [
            'lapangan_id' => $lapangan->id,
            'jenis_hari' => 'weekday',
            'harga' => 85000,
        ]);
    }
}
