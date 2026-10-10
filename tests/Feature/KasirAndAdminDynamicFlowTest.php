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

class KasirAndAdminDynamicFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Pelanggan $admin;
    protected Pelanggan $kasir;
    protected Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pelanggan::create([
            'nama' => 'Admin Venue',
            'email' => 'admin.venue@lapangin.com',
            'no_hp' => '081211112222',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->kasir = Pelanggan::create([
            'nama' => 'Kasir POS Arena',
            'email' => 'kasir.pos@lapangin.com',
            'no_hp' => '081233334444',
            'password_hash' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan 1 - Vinyl Pro',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '23:00:00',
            'deskripsi' => 'Lantai vinyl interlock',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga' => 120000,
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekend',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga' => 150000,
        ]);
    }

    public function test_kasir_can_record_walkin_booking_dynamically(): void
    {
        $payload = [
            'nama_tamu' => 'Tim Spartan FC',
            'no_wa_tamu' => '081299998888',
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '14:00',
            'durasi' => 2,
            'payType' => 'full',
        ];

        $response = $this->actingAs($this->kasir)->post(route('kasir.walkin.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('jadwals', [
            'nama_tamu' => 'Tim Spartan FC',
            'lapangan_id' => $this->lapangan->id,
            'status_pembayaran' => 'lunas_kasir',
        ]);

        $this->assertDatabaseHas('transaksis', [
            'jenis' => 'full',
            'metode' => 'tunai',
            'status' => 'berhasil',
        ]);
    }

    public function test_kasir_can_process_checkin_and_settlement_dynamically(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Budi Santos',
            'no_wa_tamu' => '081277776666',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '19:00:00',
            'jam_selesai' => '21:00:00',
            'harga_disepakati' => 240000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'pending',
            'kode_qr' => 'LPGN-TEST-9999',
            'token_manual' => 'TEST99',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 120000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $response = $this->actingAs($this->kasir)->post(route('kasir.checkin', $jadwal->id), [
            'metode' => 'tunai',
            'jumlah' => 120000,
            'catatan' => 'Pelunasan di resepsionis meja depan',
        ]);

        $response->assertRedirect();

        $jadwal->refresh();
        $this->assertSame('lunas_kasir', $jadwal->status_pembayaran);
        $this->assertSame('terverifikasi', $jadwal->status);

        $this->assertDatabaseHas('transaksis', [
            'jadwal_id' => $jadwal->id,
            'jenis' => 'pelunasan',
            'jumlah' => 120000,
            'metode' => 'tunai',
        ]);
    }

    public function test_kasir_can_approve_and_reject_reschedule_dynamically(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Dimas Pemohon',
            'no_wa_tamu' => '081255554444',
            'tanggal' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 240000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'terverifikasi',
            'created_at' => now(),
        ]);

        $reschedule = Reschedule::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_baru' => now()->addDays(3)->toDateString(),
            'jam_mulai_baru' => '14:00:00',
            'jam_selesai_baru' => '16:00:00',
            'status' => 'diajukan',
            'alasan_pengajuan' => 'Anggota tim sakit',
            'created_at' => now(),
        ]);

        // Test Approve
        $response = $this->actingAs($this->kasir)->post(route('kasir.reschedule.approve', $reschedule->id), [
            'catatan' => 'Reschedule disetujui',
        ]);

        $response->assertRedirect(route('kasir.reschedule'));

        $reschedule->refresh();
        $jadwal->refresh();

        $this->assertSame('disetujui', $reschedule->status);
        $this->assertSame($reschedule->tanggal_baru->toDateString(), $jadwal->tanggal->toDateString());
        $this->assertSame('14:00:00', $jadwal->jam_mulai);

        // Test Reject on new request
        $reschedule2 = Reschedule::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_baru' => now()->addDays(4)->toDateString(),
            'jam_mulai_baru' => '18:00:00',
            'jam_selesai_baru' => '20:00:00',
            'status' => 'diajukan',
            'alasan_pengajuan' => 'Bentrok jadwal kerja',
            'created_at' => now(),
        ]);

        $responseReject = $this->actingAs($this->kasir)->post(route('kasir.reschedule.reject', $reschedule2->id), [
            'catatan' => 'Slot bentrok',
        ]);

        $responseReject->assertRedirect(route('kasir.reschedule'));
        $reschedule2->refresh();
        $this->assertSame('ditolak', $reschedule2->status);
    }

    public function test_admin_can_store_new_kasir_account_dynamically(): void
    {
        $payload = [
            'nama' => 'Staf Baru POS',
            'email' => 'staf.baru@lapangin.id',
            'password' => 'PasswordRahasia123',
            'no_hp' => '081299990000',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.pegawai.store'), $payload);

        $response->assertRedirect(route('admin.pegawai.index'));

        $this->assertDatabaseHas('pelanggans', [
            'nama' => 'Staf Baru POS',
            'email' => 'staf.baru@lapangin.id',
            'role' => 'kasir',
        ]);
    }
}
