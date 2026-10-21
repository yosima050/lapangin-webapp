<?php

namespace Tests\Feature;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirCheckInFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Pelanggan $kasir;
    protected Pelanggan $pelanggan;
    protected Lapangan $lapangan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Akun Kasir POS Arena
        $this->kasir = Pelanggan::create([
            'nama' => 'Kasir POS Arena',
            'email' => 'kasir.pos@lapangin.com',
            'no_hp' => '081233334444',
            'password_hash' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        // 2. Buat Akun Pelanggan Reguler
        $this->pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Setia',
            'email' => 'pelanggan@lapangin.com',
            'no_hp' => '081255556666',
            'password_hash' => bcrypt('password123'),
            'role' => 'pelanggan',
        ]);

        // 3. Buat Data Lapangan & Master Harga
        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Futsal Vinyl A',
            'kategori' => 'Futsal',
            'jam_buka' => '08:00:00',
            'jam_tutup' => '23:00:00',
            'deskripsi' => 'Lantai vinyl interlock standar kompetisi',
        ]);

        HargaMaster::create([
            'lapangan_id' => $this->lapangan->id,
            'jenis_hari' => 'weekday',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '23:00:00',
            'harga' => 100000,
        ]);
    }

    /**
     * Test 1: Kasir yang terotentikasi dapat mengakses halaman scanner kamera QR.
     */
    public function test_kasir_can_access_camera_scan_page(): void
    {
        $response = $this->actingAs($this->kasir)->get(route('kasir.scan'));

        $response->assertStatus(200);
        $response->assertSee('Pemindai Kamera QR Code');
        $response->assertSee('Token Manual 6 Karakter');
        $response->assertSee('html5-qrcode');
        $response->assertSee('kasir-scanner.js');
    }

    /**
     * Test 2: Pengguna non-kasir dan tamu tidak dapat mengakses halaman scanner.
     */
    public function test_non_kasir_or_guest_cannot_access_scan_page(): void
    {
        // Tamu tanpa login dialihkan ke form login
        $guestResponse = $this->get(route('kasir.scan'));
        $guestResponse->assertRedirect(route('login'));

        // Pelanggan biasa ditolak oleh middleware IsKasir
        $pelangganResponse = $this->actingAs($this->pelanggan)->get(route('kasir.scan'));
        $this->assertTrue(
            in_array($pelangganResponse->getStatusCode(), [302, 403]),
            "Non-kasir harus ditolak dengan status 302 redirect atau 403 forbidden."
        );
    }

    /**
     * Test 3: Kasir dapat melakukan lookup tiket valid menggunakan QR Code.
     */
    public function test_kasir_can_lookup_valid_ticket_using_qr_code(): void
    {
        $qrCode = 'LPNG-QR-VALID-12345';
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'pelanggan_id' => $this->pelanggan->id,
            'nama_tamu' => $this->pelanggan->nama,
            'no_wa_tamu' => $this->pelanggan->no_hp,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '14:00:00',
            'jam_selesai' => '16:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'pending',
            'kode_qr' => $qrCode,
            'token_manual' => 'VAL123',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 100000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        $response = $this->actingAs($this->kasir)->getJson(route('kasir.ticket.lookup', ['code' => $qrCode]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_valid' => true,
            'is_expired' => false,
            'is_duplicate' => false,
            'status_label' => 'Tiket Valid',
            'jadwal' => [
                'kode_qr' => $qrCode,
                'token_manual' => 'VAL123',
                'nama_penyewa' => $this->pelanggan->nama,
                'total_harga' => 200000,
                'dp_paid' => 100000,
                'sisa_tagihan' => 100000,
            ],
        ]);
    }

    /**
     * Test 4: Form token manual berfungsi normal mencari tiket dengan 6 karakter alfanumerik.
     */
    public function test_kasir_can_lookup_valid_ticket_using_manual_6_char_token(): void
    {
        $tokenManual = 'TOK666';
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Ahmad Tim Pro',
            'no_wa_tamu' => '081299990000',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '16:00:00',
            'jam_selesai' => '18:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'pending',
            'kode_qr' => 'LPNG-QR-TOK-999',
            'token_manual' => $tokenManual,
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // Uji lookup case-insensitive (misal input kasir lowercase 'tok666')
        $response = $this->actingAs($this->kasir)->getJson(route('kasir.ticket.lookup', ['code' => strtolower($tokenManual)]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_valid' => true,
            'jadwal' => [
                'token_manual' => $tokenManual,
                'nama_penyewa' => 'Ahmad Tim Pro',
            ],
        ]);
    }

    /**
     * Test 5: Lookup mengembalikan 404 jika tiket tidak ditemukan.
     */
    public function test_lookup_returns_404_when_ticket_not_found(): void
    {
        $response = $this->actingAs($this->kasir)->getJson(route('kasir.ticket.lookup', ['code' => 'TIDAK_ADA_999']));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test 6: Skenario Tiket Valid - Check-in berhasil dan pelunasan onsite tercatat.
     */
    public function test_kasir_can_successfully_checkin_valid_ticket_with_settlement(): void
    {
        $jadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Budi FC',
            'no_wa_tamu' => '081277778888',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '19:00:00',
            'jam_selesai' => '21:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'pending',
            'kode_qr' => 'LPNG-QR-BUDI-111',
            'token_manual' => 'BUDI11',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        Transaksi::create([
            'jadwal_id' => $jadwal->id,
            'jenis' => 'dp',
            'jumlah' => 100000,
            'metode' => 'online',
            'status' => 'berhasil',
            'dibayar_pada' => now(),
        ]);

        // Kasir memproses pelunasan Rp 100.000 via tunai di meja kasir
        $response = $this->actingAs($this->kasir)->post(route('kasir.checkin', $jadwal->id), [
            'metode' => 'tunai',
            'jumlah' => 100000,
            'catatan' => 'Pelunasan tunai di kasir meja 1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $jadwal->refresh();
        $this->assertSame('lunas_kasir', $jadwal->status_pembayaran);
        $this->assertSame('terverifikasi', $jadwal->status);

        $this->assertDatabaseHas('transaksis', [
            'jadwal_id' => $jadwal->id,
            'jenis' => 'pelunasan',
            'jumlah' => 100000,
            'metode' => 'tunai',
            'status' => 'berhasil',
        ]);
    }

    /**
     * Test 7: Skenario Tiket Kedaluwarsa (Expired) - Ditolak dan tidak dapat check-in.
     */
    public function test_checkin_fails_and_is_rejected_for_expired_ticket(): void
    {
        // Buat tiket dengan tanggal lampau (H-1)
        $expiredJadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Tamu Terlambat',
            'no_wa_tamu' => '081299991111',
            'tanggal' => now()->subDays(1)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'harga_disepakati' => 150000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'dp_terverifikasi',
            'status' => 'pending',
            'kode_qr' => 'LPNG-QR-EXPIRED-99',
            'token_manual' => 'EXP999',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now()->subDays(1),
        ]);

        // 1. Verifikasi deteksi status kedaluwarsa pada API Lookup
        $lookupResponse = $this->actingAs($this->kasir)->getJson(route('kasir.ticket.lookup', ['code' => 'LPNG-QR-EXPIRED-99']));
        $lookupResponse->assertStatus(200);
        $lookupResponse->assertJson([
            'success' => true,
            'is_valid' => false,
            'is_expired' => true,
            'status_label' => 'Tiket Kedaluwarsa',
        ]);

        // 2. Verifikasi penolakan eksekusi check-in via form POST (Redirect with error)
        $checkinResponse = $this->actingAs($this->kasir)->post(route('kasir.checkin', $expiredJadwal->id), [
            'metode' => 'tunai',
            'jumlah' => 150000,
        ]);

        $checkinResponse->assertRedirect();
        $checkinResponse->assertSessionHas('error');

        // Pastikan status jadwal TIDAK berubah menjadi lunas_kasir
        $expiredJadwal->refresh();
        $this->assertNotSame('lunas_kasir', $expiredJadwal->status_pembayaran);

        // 3. Verifikasi penolakan eksekusi check-in via JSON (422 Unprocessable Content)
        $jsonCheckinResponse = $this->actingAs($this->kasir)->postJson(route('kasir.checkin', $expiredJadwal->id), [
            'metode' => 'tunai',
            'jumlah' => 150000,
        ]);

        $jsonCheckinResponse->assertStatus(422);
        $jsonCheckinResponse->assertJson([
            'success' => false,
            'error' => 'expired',
        ]);
    }

    /**
     * Test 8: Skenario Tiket Duplikat (Already Used) - Ditolak jika tiket sudah pernah check-in sebelumnya.
     */
    public function test_checkin_fails_and_is_rejected_for_duplicate_ticket(): void
    {
        // Buat tiket yang sudah lunas_kasir (sudah pernah check-in)
        $duplicateJadwal = Jadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'nama_tamu' => 'Tamu Sudah Checkin',
            'no_wa_tamu' => '081288882222',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '15:00:00',
            'harga_disepakati' => 200000,
            'biaya_layanan' => 0,
            'status_pembayaran' => 'lunas_kasir', // Sudah check-in
            'status' => 'terverifikasi',
            'kode_qr' => 'LPNG-QR-DUPLICATE-77',
            'token_manual' => 'DUP777',
            'sewa_rompi' => 0,
            'sewa_bola' => 0,
            'denda_pembatalan' => 0,
            'created_at' => now(),
        ]);

        // 1. Verifikasi deteksi status duplikat pada API Lookup
        $lookupResponse = $this->actingAs($this->kasir)->getJson(route('kasir.ticket.lookup', ['code' => 'DUP777']));
        $lookupResponse->assertStatus(200);
        $lookupResponse->assertJson([
            'success' => true,
            'is_valid' => false,
            'is_duplicate' => true,
            'status_label' => 'Tiket Sudah Digunakan (Duplikat)',
        ]);

        // 2. Verifikasi penolakan percobaan check-in ulang via form POST
        $checkinResponse = $this->actingAs($this->kasir)->post(route('kasir.checkin', $duplicateJadwal->id), [
            'metode' => 'tunai',
            'jumlah' => 0,
        ]);

        $checkinResponse->assertRedirect();
        $checkinResponse->assertSessionHas('error');

        // Pastikan tidak ada transaksi pelunasan baru yang tercatat ganda
        $this->assertDatabaseMissing('transaksis', [
            'jadwal_id' => $duplicateJadwal->id,
            'jenis' => 'pelunasan',
        ]);

        // 3. Verifikasi penolakan check-in duplikat via JSON (422 Unprocessable Content)
        $jsonCheckinResponse = $this->actingAs($this->kasir)->postJson(route('kasir.checkin', $duplicateJadwal->id), [
            'metode' => 'tunai',
            'jumlah' => 0,
        ]);

        $jsonCheckinResponse->assertStatus(422);
        $jsonCheckinResponse->assertJson([
            'success' => false,
            'error' => 'duplicate',
        ]);
    }
}

