<?php

namespace Database\Seeders;

use App\Models\HargaMaster;
use App\Models\Jadwal;
use App\Models\Lapangan;
use App\Models\Pelanggan;
use App\Models\Reschedule;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin, Kasir, dan Pelanggan
        $admin = Pelanggan::firstOrCreate(
            ['email' => 'admin@lapangin.com'],
            [
                'nama' => 'Admin Lapangin',
                'no_hp' => '081234567890',
                'password_hash' => Hash::make('admin1'),
                'role' => 'admin',
                'created_at' => now(),
            ]
        );

        $kasirDefault = Pelanggan::firstOrCreate(
            ['email' => 'kasir@lapangin.com'],
            [
                'nama' => 'Kasir Lapangin',
                'no_hp' => '081234567899',
                'password_hash' => Hash::make('kasir1'),
                'role' => 'kasir',
                'created_at' => now(),
            ]
        );

        $kasir1 = Pelanggan::firstOrCreate(
            ['email' => 'bima.kasir@lapangin.id'],
            [
                'nama' => 'Bima Kasir',
                'no_hp' => '081234567891',
                'password_hash' => Hash::make('kasir1'),
                'role' => 'kasir',
                'created_at' => now(),
            ]
        );

        $kasir2 = Pelanggan::firstOrCreate(
            ['email' => 'siti.kasir@lapangin.id'],
            [
                'nama' => 'Siti Rahma',
                'no_hp' => '081234567892',
                'password_hash' => Hash::make('kasir1'),
                'role' => 'kasir',
                'created_at' => now(),
            ]
        );

        $memberDimas = Pelanggan::firstOrCreate(
            ['email' => 'dimas.pratama@gmail.com'],
            [
                'nama' => 'Dimas Prasetyo',
                'no_hp' => '0812-9845-7721',
                'password_hash' => Hash::make('password'),
                'role' => 'pelanggan',
                'created_at' => now(),
            ]
        );

        $memberBudi = Pelanggan::firstOrCreate(
            ['email' => 'budi.santoso@gmail.com'],
            [
                'nama' => 'Budi Santoso',
                'no_hp' => '0813-8899-7711',
                'password_hash' => Hash::make('password'),
                'role' => 'pelanggan',
                'created_at' => now(),
            ]
        );

        $memberSiti = Pelanggan::firstOrCreate(
            ['email' => 'siti.member@gmail.com'],
            [
                'nama' => 'Siti Rahma Member',
                'no_hp' => '0815-4422-9900',
                'password_hash' => Hash::make('password'),
                'role' => 'pelanggan',
                'created_at' => now(),
            ]
        );

        // 2. Data Lapangan (Venue Utama POS & Venue Desain Katalog Widi)
        $lapangan1 = Lapangan::firstOrCreate(
            ['nama' => 'Lapangan 1 - Vinyl Pro (Futsal Indoor)'],
            [
                'kategori' => 'Futsal',
                'jam_buka' => '07:00',
                'jam_tutup' => '24:00',
                'deskripsi' => 'Lantai Vinyl Tarkett 8mm standar AFC, jaring pengaman keliling, papan skor digital wireless, pencahayaan LED turnamen.',
                'foto' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        $lapangan2 = Lapangan::firstOrCreate(
            ['nama' => 'Lapangan 2 - Interlock Premium (Futsal & Basket)'],
            [
                'kategori' => 'Futsal & Basket',
                'jam_buka' => '07:00',
                'jam_tutup' => '24:00',
                'deskripsi' => 'Lantai Interlock Modular Polypropylene anti-slip, ring basket hidrolik standar Perbasi, LED Sports Floodlight 400W.',
                'foto' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        $lapangan3 = Lapangan::firstOrCreate(
            ['nama' => 'Badminton Court 1 & 2 (Karpet Yonex)'],
            [
                'kategori' => 'Badminton',
                'jam_buka' => '07:00',
                'jam_tutup' => '24:00',
                'deskripsi' => 'Karpet Standar BWF Certified 4.5mm, lampu LED anti silau horizontal, kursi wasit standar turnamen internasional.',
                'foto' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        $lapanganViva = Lapangan::firstOrCreate(
            ['nama' => 'Viva Futsal Arena Malang'],
            [
                'kategori' => 'Futsal',
                'jam_buka' => '08:00',
                'jam_tutup' => '23:00',
                'deskripsi' => 'Arena futsal modern dengan rumput sintetis lembut dan ruang ganti ber-AC.',
                'foto' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        $lapanganSmash = Lapangan::firstOrCreate(
            ['nama' => 'Badminton Smash Arena Malang'],
            [
                'kategori' => 'Badminton',
                'jam_buka' => '08:00',
                'jam_tutup' => '23:00',
                'deskripsi' => 'Gedung olahraga badminton 6 lapangan berstandar nasional dengan kantin.',
                'foto' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        $lapanganGaruda = Lapangan::firstOrCreate(
            ['nama' => 'Garuda Mini Soccer Stadium Malang'],
            [
                'kategori' => 'Mini Soccer',
                'jam_buka' => '06:00',
                'jam_tutup' => '23:00',
                'deskripsi' => 'Stadion mini soccer rumput alami berstandar FIFA dengan tribun penonton.',
                'foto' => 'https://images.unsplash.com/photo-1556056504-5c7696c4c28d?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        $lapanganSupreme = Lapangan::firstOrCreate(
            ['nama' => 'Supreme Futsal & Padel Hub Malang'],
            [
                'kategori' => 'Futsal',
                'jam_buka' => '08:00',
                'jam_tutup' => '24:00',
                'deskripsi' => 'Hub olahraga terpadu futsal dan padel dengan pro-shop dan cafe.',
                'foto' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1200&q=80',
                'created_at' => now(),
            ]
        );

        // 3. Aturan Harga Master (Weekday, Weekend, & Tanggal Khusus Libur)
        $allLapangans = [$lapangan1, $lapangan2, $lapangan3, $lapanganViva, $lapanganSmash, $lapanganGaruda, $lapanganSupreme];
        $rates = [
            $lapangan1->id => ['weekday' => 120000, 'weekend' => 200000],
            $lapangan2->id => ['weekday' => 140000, 'weekend' => 190000],
            $lapangan3->id => ['weekday' => 60000, 'weekend' => 90000],
            $lapanganViva->id => ['weekday' => 150000, 'weekend' => 180000],
            $lapanganSmash->id => ['weekday' => 50000, 'weekend' => 75000],
            $lapanganGaruda->id => ['weekday' => 350000, 'weekend' => 500000],
            $lapanganSupreme->id => ['weekday' => 160000, 'weekend' => 220000],
        ];

        foreach ($allLapangans as $lap) {
            HargaMaster::firstOrCreate(
                [
                    'lapangan_id' => $lap->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => '07:00',
                    'jam_selesai' => '24:00',
                ],
                [
                    'harga' => $rates[$lap->id]['weekday'],
                ]
            );

            HargaMaster::firstOrCreate(
                [
                    'lapangan_id' => $lap->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => '07:00',
                    'jam_selesai' => '24:00',
                ],
                [
                    'harga' => $rates[$lap->id]['weekend'],
                ]
            );

            // Surcharge Hari Libur Nasional
            $holidays = [
                ['tanggal' => Carbon::parse('2026-08-17'), 'surcharge' => 25000],
                ['tanggal' => Carbon::parse('2026-06-06'), 'surcharge' => 25000],
                ['tanggal' => Carbon::parse('2026-07-08'), 'surcharge' => 25000],
            ];

            foreach ($holidays as $h) {
                HargaMaster::firstOrCreate(
                    [
                        'lapangan_id' => $lap->id,
                        'tanggal_khusus' => $h['tanggal']->toDateString(),
                        'jam_mulai' => '07:00',
                        'jam_selesai' => '24:00',
                    ],
                    [
                        'jenis_hari' => 'weekend',
                        'harga' => $rates[$lap->id]['weekend'] + $h['surcharge'],
                    ]
                );
            }
        }

        // 4. Data Jadwal Nyata Hari Ini & Booking Aktif
        $today = now()->toDateString();

        // Booking 1: Dimas Prasetyo (Spartan FC) - DP 50%, Menunggu Pelunasan di Kasir
        $jadwalDimas = Jadwal::firstOrCreate(
            ['kode_qr' => 'LPGN-20250513-8821'],
            [
                'lapangan_id' => $lapangan1->id,
                'pelanggan_id' => $memberDimas->id,
                'nama_tamu' => 'Dimas Prasetyo (Spartan FC)',
                'no_wa_tamu' => '0812-9845-7721',
                'email_tamu' => 'dimas.pratama@gmail.com',
                'tanggal' => $today,
                'jam_mulai' => '19:00:00',
                'jam_selesai' => '21:00:00',
                'harga_disepakati' => 360000,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'dp_terverifikasi',
                'status' => 'terverifikasi',
                'token_manual' => 'LPGN-8821',
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'catatan_kasir' => 'Termasuk rompi latihan & bola match seri Pro.',
                'created_at' => now()->subHours(3),
            ]
        );

        // Transaksi DP Online untuk Dimas
        Transaksi::firstOrCreate(
            ['jadwal_id' => $jadwalDimas->id, 'jenis' => 'dp'],
            [
                'jumlah' => 180000,
                'metode' => 'online',
                'status' => 'berhasil',
                'referensi_midtrans' => 'MID-DP-8821',
                'dibayar_pada' => now()->subHours(3),
            ]
        );

        // Booking 2: Komunitas Garuda FC (Coach Andri) - Lunas Online
        $jadwalGaruda = Jadwal::firstOrCreate(
            ['kode_qr' => 'LPGN-20250513-3390'],
            [
                'lapangan_id' => $lapangan1->id,
                'pelanggan_id' => $memberBudi->id,
                'nama_tamu' => 'Komunitas Garuda FC',
                'no_wa_tamu' => '0812-9988-1204',
                'email_tamu' => 'andri@garudafc.com',
                'tanggal' => $today,
                'jam_mulai' => '16:00:00',
                'jam_selesai' => '18:00:00',
                'harga_disepakati' => 360000,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'lunas_online',
                'status' => 'lunas',
                'token_manual' => 'LPGN-3390',
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'catatan_kasir' => null,
                'created_at' => now()->subHours(6),
            ]
        );

        Transaksi::firstOrCreate(
            ['jadwal_id' => $jadwalGaruda->id, 'jenis' => 'full'],
            [
                'jumlah' => 360000,
                'metode' => 'online',
                'status' => 'berhasil',
                'referensi_midtrans' => 'MID-FULL-3390',
                'dibayar_pada' => now()->subHours(6),
            ]
        );

        // Booking 3: Walk-in Mitra FC - Lunas Kasir Tunai
        $jadwalMitra = Jadwal::firstOrCreate(
            ['kode_qr' => 'LPGN-20250513-1102'],
            [
                'lapangan_id' => $lapangan1->id,
                'pelanggan_id' => null,
                'nama_tamu' => 'Walk-in Mitra FC',
                'no_wa_tamu' => '0857-1122-3344',
                'email_tamu' => null,
                'tanggal' => $today,
                'jam_mulai' => '21:00:00',
                'jam_selesai' => '22:00:00',
                'harga_disepakati' => 240000,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'lunas_kasir',
                'status' => 'lunas',
                'token_manual' => 'LPGN-1102',
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'catatan_kasir' => 'Transaksi Meja Resepsionis #01',
                'created_at' => now()->subHours(1),
            ]
        );

        Transaksi::firstOrCreate(
            ['jadwal_id' => $jadwalMitra->id, 'jenis' => 'full'],
            [
                'jumlah' => 240000,
                'metode' => 'tunai',
                'status' => 'berhasil',
                'referensi_midtrans' => 'POS-CASH-1102',
                'dibayar_pada' => now()->subHours(1),
            ]
        );

        // 5. Data Reschedule Nyata
        $jadwalReschedule1 = Jadwal::firstOrCreate(
            ['kode_qr' => 'LPGN-RESCH-001'],
            [
                'lapangan_id' => $lapangan1->id,
                'pelanggan_id' => $memberDimas->id,
                'nama_tamu' => 'Dimas Pratama',
                'no_wa_tamu' => '+62 812-3456-7890',
                'email_tamu' => 'dimas.pratama@gmail.com',
                'tanggal' => Carbon::parse('2026-10-18')->toDateString(),
                'jam_mulai' => '19:00:00',
                'jam_selesai' => '20:00:00',
                'harga_disepakati' => 175000,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'dp_terverifikasi',
                'status' => 'pending',
                'token_manual' => 'LP-8821',
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'created_at' => now()->subHours(2),
            ]
        );

        Reschedule::firstOrCreate(
            ['jadwal_id' => $jadwalReschedule1->id],
            [
                'tanggal_baru' => Carbon::parse('2026-10-19')->toDateString(),
                'jam_mulai_baru' => '20:00:00',
                'jam_selesai_baru' => '21:00:00',
                'status' => 'diajukan',
                'alasan_pengajuan' => 'Anggota tim ada kegiatan mendadak hari Sabtu, ingin geser ke Minggu malam jam 8.',
            ]
        );

        $jadwalReschedule2 = Jadwal::firstOrCreate(
            ['kode_qr' => 'LPGN-RESCH-002'],
            [
                'lapangan_id' => $lapangan2->id,
                'pelanggan_id' => $memberBudi->id,
                'nama_tamu' => 'Budi Santoso',
                'no_wa_tamu' => '+62 813-8899-7711',
                'email_tamu' => 'budi.santoso@gmail.com',
                'tanggal' => Carbon::parse('2026-10-20')->toDateString(),
                'jam_mulai' => '18:00:00',
                'jam_selesai' => '19:00:00',
                'harga_disepakati' => 140000,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'dp_terverifikasi',
                'status' => 'pending',
                'token_manual' => 'LP-3391',
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'created_at' => now()->subHours(3)->subMinutes(30),
            ]
        );

        Reschedule::firstOrCreate(
            ['jadwal_id' => $jadwalReschedule2->id],
            [
                'tanggal_baru' => Carbon::parse('2026-10-21')->toDateString(),
                'jam_mulai_baru' => '19:00:00',
                'jam_selesai_baru' => '20:00:00',
                'status' => 'diajukan',
                'alasan_pengajuan' => 'Bentrok jadwal meeting kantor mendadak, geser ke Selasa malam.',
            ]
        );

        $jadwalReschedule3 = Jadwal::firstOrCreate(
            ['kode_qr' => 'LPGN-RESCH-003'],
            [
                'lapangan_id' => $lapangan3->id,
                'pelanggan_id' => $memberSiti->id,
                'nama_tamu' => 'Siti Rahma Member',
                'no_wa_tamu' => '+62 815-4422-9900',
                'email_tamu' => 'siti.member@gmail.com',
                'tanggal' => Carbon::parse('2026-10-19')->toDateString(),
                'jam_mulai' => '10:00:00',
                'jam_selesai' => '11:00:00',
                'harga_disepakati' => 90000,
                'biaya_layanan' => 0,
                'status_pembayaran' => 'dp_terverifikasi',
                'status' => 'pending',
                'token_manual' => 'LP-9902',
                'sewa_rompi' => 0,
                'sewa_bola' => 0,
                'denda_pembatalan' => 0,
                'created_at' => now()->subHours(5),
            ]
        );

        Reschedule::firstOrCreate(
            ['jadwal_id' => $jadwalReschedule3->id],
            [
                'tanggal_baru' => Carbon::parse('2026-10-19')->toDateString(),
                'jam_mulai_baru' => '15:00:00',
                'jam_selesai_baru' => '16:00:00',
                'status' => 'diajukan',
                'alasan_pengajuan' => 'Rekan sparing badminton baru bisa hadir di sesi sore jam 3.',
            ]
        );
    }
}