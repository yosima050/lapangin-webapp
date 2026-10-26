<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use App\Services\BookingAdjustmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingAdjustmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingAdjustmentService $service;
    protected Pelanggan $pelanggan;
    protected Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BookingAdjustmentService::class);

        $this->pelanggan = Pelanggan::create([
            'nama' => 'Bima Pelanggan',
            'email' => 'bima@example.com',
            'no_hp' => '081299998888',
            'password_hash' => bcrypt('secret123'),
            'role' => 'pelanggan',
        ]);

        $this->lapangan = Lapangan::create([
            'nama' => 'Arena Futsal Interlock A',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '23:00:00',
            'deskripsi' => 'Lapangan standar nasional',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga' => 150000,
        ]);
    }

    public function test_reschedule_eligibility_passes_for_future_active_booking(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $check = $this->service->checkRescheduleEligibility($jadwal);

        $this->assertTrue($check['eligible']);
        $this->assertNull($check['reason']);
        $this->assertGreaterThanOrEqual(24, $check['hours_until_kickoff']);
    }

    public function test_reschedule_eligibility_fails_when_under_24_hours_before_kickoff(): void
    {
        // Jadwal hanya 6 jam dari sekarang
        $kickoff = now()->addHours(6);
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => $kickoff->toDateString(),
            'jam_mulai' => $kickoff->format('H:i:s'),
            'jam_selesai' => $kickoff->copy()->addHours(2)->format('H:i:s'),
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $check = $this->service->checkRescheduleEligibility($jadwal);

        $this->assertFalse($check['eligible']);
        $this->assertStringContainsString('maksimal H-1', $check['reason']);
    }

    public function test_reschedule_eligibility_fails_when_already_checked_in(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '14:00:00',
            'jam_selesai' => '16:00:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'checked_in_at' => now(),
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $check = $this->service->checkRescheduleEligibility($jadwal);

        $this->assertFalse($check['eligible']);
        $this->assertStringContainsString('check-in', $check['reason']);
    }

    public function test_customer_can_request_reschedule_successfully(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(4)->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $tanggalBaru = now()->addDays(5)->toDateString();
        $reschedule = $this->service->requestReschedule(
            $jadwal,
            $tanggalBaru,
            '13:00',
            '15:00',
            'Tim berhalangan hadir di pagi hari.'
        );

        $this->assertInstanceOf(Reschedule::class, $reschedule);
        $this->assertEquals('diajukan', $reschedule->status);
        $this->assertEquals('13:00:00', $reschedule->jam_mulai_baru);
        $this->assertEquals('15:00:00', $reschedule->jam_selesai_baru);

        // Slot target sekarang terkunci dari permohonan reschedule lain
        $isAvailable = $this->service->checkSlotAvailability(
            $this->lapangan,
            $tanggalBaru,
            '14:00',
            '16:00'
        );
        $this->assertFalse($isAvailable);
    }

    public function test_kasir_can_approve_reschedule_and_updates_jadwal(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'harga_disepakati' => 300000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_online',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        $tanggalBaru = now()->addDays(6)->toDateString();
        $reschedule = Reschedule::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_baru' => $tanggalBaru,
            'jam_mulai_baru' => '16:00:00',
            'jam_selesai_baru' => '18:00:00',
            'status' => 'diajukan',
            'alasan_pengajuan' => 'Ganti jam sore',
            'created_at' => now(),
        ]);

        $updatedJadwal = $this->service->approveReschedule($reschedule, 'Disetujui Kasir');

        $this->assertEquals($tanggalBaru, $updatedJadwal->tanggal->toDateString());
        $this->assertEquals('16:00:00', $updatedJadwal->jam_mulai);
        $this->assertEquals('18:00:00', $updatedJadwal->jam_selesai);
        $this->assertEquals('disetujui', $reschedule->fresh()->status);
    }

    public function test_auto_penalty_calculation_for_planned_cancellation_over_24h(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => now()->addDays(3)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 200000,
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
            'jumlah' => 100000,
            'metode' => 'midtrans',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $penalty = $this->service->calculateCancellationPenalty($jadwal);

        $this->assertEquals('over_24h', $penalty['policy_rule']);
        $this->assertFalse($penalty['is_h_minus_24']);
        // 10% dari 200.000 = 20.000
        $this->assertEquals(20000.0, $penalty['penalty_amount']);
        // Total bayar 100.000 - denda 20.000 = refund 80.000
        $this->assertEquals(80000.0, $penalty['estimated_refund']);
    }

    public function test_auto_penalty_calculation_for_sudden_cancellation_under_24h(): void
    {
        $kickoff = now()->addHours(8);
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => $kickoff->toDateString(),
            'jam_mulai' => $kickoff->format('H:i:s'),
            'jam_selesai' => $kickoff->copy()->addHours(2)->format('H:i:s'),
            'harga_disepakati' => 200000,
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
            'jumlah' => 100000,
            'metode' => 'midtrans',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $penalty = $this->service->calculateCancellationPenalty($jadwal);

        $this->assertEquals('under_24h', $penalty['policy_rule']);
        $this->assertTrue($penalty['is_h_minus_24']);
        // Hangus DP: 50% dari 200.000 = 100.000
        $this->assertEquals(100000.0, $penalty['penalty_amount']);
        // Total bayar 100.000 - denda 100.000 = refund 0
        $this->assertEquals(0.0, $penalty['estimated_refund']);
    }

    public function test_cancellation_releases_slot_and_records_audit_trail(): void
    {
        $tanggal = now()->addDays(2)->toDateString();
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // Awalnya slot sibuk
        $this->assertFalse(
            $this->service->checkSlotAvailability($this->lapangan, $tanggal, '10:00', '12:00')
        );

        // Eksekusi pembatalan
        $result = $this->service->cancelBooking($jadwal, 'Hujan deras mendadak', $this->pelanggan);

        $this->assertEquals('dibatalkan', $result['jadwal']->status);
        $this->assertGreaterThan(0, $result['jadwal']->denda_pembatalan);
        $this->assertStringContainsString('Dibatalkan oleh', $result['jadwal']->catatan_kasir);

        // Slot jadwal kini otomatis TERSEDIA kembali ke publik
        $this->assertTrue(
            $this->service->checkSlotAvailability($this->lapangan, $tanggal, '10:00', '12:00')
        );
    }
}
