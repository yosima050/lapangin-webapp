<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganRescheduleAndCancelTest extends TestCase
{
    use RefreshDatabase;

    protected Pelanggan $pelanggan;
    protected Pelanggan $pelangganLain;
    protected Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelanggan = Pelanggan::create([
            'nama' => 'Yosep Pelanggan',
            'email' => 'yosep.customer@lapangin.id',
            'no_hp' => '081211112222',
            'password_hash' => bcrypt('password123'),
            'role' => 'pelanggan',
        ]);

        $this->pelangganLain = Pelanggan::create([
            'nama' => 'User Lain',
            'email' => 'other@lapangin.id',
            'no_hp' => '081299990000',
            'password_hash' => bcrypt('password123'),
            'role' => 'pelanggan',
        ]);

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Rumput Sintetis B',
            'kategori' => 'Mini Soccer',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '23:00:00',
            'deskripsi' => 'Rumput sintetis premium',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga' => 200000,
        ]);
    }

    public function test_customer_can_view_reschedule_page_for_their_own_booking(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'harga_disepakati' => 400000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)
            ->get(route('pelanggan.booking.reschedule', $jadwal->id));

        $response->assertOk();
        $response->assertSee('Reschedule Jadwal Bermain');
        $response->assertSee('Lapangan Rumput Sintetis B');
    }

    public function test_customer_cannot_access_other_users_reschedule_page(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelangganLain->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'harga_disepakati' => 400000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)
            ->get(route('pelanggan.booking.reschedule', $jadwal->id));

        $response->assertNotFound();
    }

    public function test_api_check_slots_returns_hourly_availability(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 400000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $targetDate = now()->addDays(4)->toDateString();

        $response = $this->actingAs($this->pelanggan)
            ->getJson(route('pelanggan.booking.reschedule.slots', [
                'id' => $jadwal->id,
                'tanggal' => $targetDate,
            ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'tanggal',
            'durasi_jam',
            'slots' => [
                '*' => ['jam_mulai', 'jam_selesai', 'label', 'is_available'],
            ],
        ]);
    }

    public function test_customer_can_submit_reschedule_request(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 400000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $tanggalBaru = now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.reschedule.store', $jadwal->id), [
                'tanggal_baru' => $tanggalBaru,
                'jam_mulai_baru' => '14:00',
                'jam_selesai_baru' => '16:00',
                'alasan_pengajuan' => 'Anggota tim memiliki jadwal kegiatan lain di pagi hari.',
            ]);

        $response->assertRedirect(route('pelanggan.riwayat'));
        $response->assertSessionHas('success');

        $reschedule = Reschedule::where('jadwal_id', $jadwal->id)->first();
        $this->assertNotNull($reschedule);
        $this->assertEquals($tanggalBaru, $reschedule->tanggal_baru->toDateString());
        $this->assertEquals('14:00:00', $reschedule->jam_mulai_baru);
        $this->assertEquals('16:00:00', $reschedule->jam_selesai_baru);
        $this->assertEquals('diajukan', $reschedule->status);
    }

    public function test_cancel_preview_api_calculates_correct_auto_penalty(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 400000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 200000,
            'metode' => 'midtrans',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)
            ->getJson(route('pelanggan.booking.cancel.preview', $jadwal->id));

        $response->assertOk();
        $response->assertJson([
            'jadwal_id' => $jadwal->id,
            'penalty' => [
                'policy_rule' => 'over_24h',
                'penalty_percentage' => 10,
                'penalty_amount' => 40000,
                'estimated_refund' => 160000,
            ],
        ]);
    }

    public function test_customer_can_cancel_booking_and_releases_slot(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '15:00:00',
            'jam_selesai' => '17:00:00',
            'harga_disepakati' => 400000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 200000,
            'metode' => 'midtrans',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.cancel', $jadwal->id), [
                'alasan_pembatalan' => 'Ada acara mendadak keluarga.',
            ]);

        $response->assertRedirect(route('pelanggan.riwayat'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('jadwals', [
            'id' => $jadwal->id,
            'status' => 'dibatalkan',
            'denda_pembatalan' => 40000,
        ]);
    }

    public function test_api_check_slots_marks_slot_as_too_close_if_less_than_24h(): void
    {
        // Set waktu pengujian ke jam 16:00
        \Carbon\Carbon::setTestNow('2026-10-26 16:00:00');

        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => '2026-10-29',
            'jam_mulai' => '18:00:00',
            'jam_selesai' => '19:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // Cek ketersediaan untuk besok (27 Okt 2026)
        $response = $this->actingAs($this->pelanggan)
            ->getJson(route('pelanggan.booking.reschedule.slots', [
                'id' => $jadwal->id,
                'tanggal' => '2026-10-27',
            ]));

        $response->assertOk();
        $slots = collect($response->json('slots'));

        // Slot 08:00 besok (hanya 16 jam dari sekarang) harus is_available = false dan is_too_close = true
        $slotPagi = $slots->firstWhere('jam_mulai', '08:00');
        $this->assertNotNull($slotPagi);
        $this->assertFalse($slotPagi['is_available']);
        $this->assertTrue($slotPagi['is_too_close']);

        // Slot 17:00 besok (25 jam dari sekarang) harus is_available = true dan is_too_close = false
        $slotSore = $slots->firstWhere('jam_mulai', '17:00');
        $this->assertNotNull($slotSore);
        $this->assertTrue($slotSore['is_available']);
        $this->assertFalse($slotSore['is_too_close']);

        \Carbon\Carbon::setTestNow();
    }

    public function test_customer_cannot_submit_reschedule_slot_less_than_24h_from_now(): void
    {
        \Carbon\Carbon::setTestNow('2026-10-26 16:00:00');

        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => '2026-10-29',
            'jam_mulai' => '18:00:00',
            'jam_selesai' => '19:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // Coba ajukan slot besok pagi jam 08:00 (hanya 16 jam lagi)
        $response = $this->actingAs($this->pelanggan)
            ->post(route('pelanggan.booking.reschedule.store', $jadwal->id), [
                'tanggal_baru' => '2026-10-27',
                'jam_mulai_baru' => '08:00',
                'jam_selesai_baru' => '09:00',
                'alasan_pengajuan' => 'Ingin bermain lebih pagi besok.',
            ]);

        $response->assertSessionHasErrors(['jam_mulai_baru']);

        \Carbon\Carbon::setTestNow();
    }
}
