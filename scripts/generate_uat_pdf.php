<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Skenario UAT — LapangIn</title>
    <style>
        @page {
            margin: 18mm 14mm 18mm 14mm;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
            }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.45;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header-brand {
            border-bottom: 2px solid #1a146b;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 18pt;
            font-weight: 800;
            color: #1a146b;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 9.5pt;
            font-weight: 600;
            color: #416900;
            margin: 2px 0 8px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 14px;
            background: #f8faff;
            border: 1px solid #dbeafe;
            border-radius: 4px;
        }
        .meta-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
        }
        .meta-table td.label {
            font-weight: bold;
            color: #1e3a8a;
            width: 22%;
            background: #eff6ff;
        }
        h2 {
            font-size: 11pt;
            font-weight: 800;
            color: #1a146b;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        h3 {
            font-size: 10pt;
            font-weight: 700;
            color: #0f172a;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        h4 {
            font-size: 9pt;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 10px;
            margin-bottom: 4px;
            background: #f1f5f9;
            padding: 3px 6px;
            border-left: 3px solid #1a146b;
        }
        p {
            margin: 0 0 6px 0;
            text-align: justify;
        }
        ul {
            margin: 4px 0 8px 16px;
            padding: 0;
        }
        li {
            margin-bottom: 3px;
        }
        .alert-box {
            background: #eff6ff;
            border-left: 4px solid #1a146b;
            padding: 8px 10px;
            margin: 8px 0;
            font-size: 8.5pt;
            color: #1e293b;
        }
        .alert-box strong {
            color: #1a146b;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin: 6px 0 12px 0;
            page-break-inside: avoid;
        }
        table.data-table th {
            background: #1a146b;
            color: #ffffff;
            font-weight: 700;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #1a146b;
            font-size: 7.5pt;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 4.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .badge-status {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7.5pt;
        }
        .badge-green {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .checkbox-cell {
            text-align: center;
            font-weight: bold;
            font-family: monospace;
            color: #475569;
            width: 32px;
        }
        .page-break {
            page-break-after: always;
        }
        .footer-note {
            font-size: 7.5pt;
            color: #64748b;
            text-align: center;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <div class="header-brand">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0;">
                    <h1 class="brand-title">Dokumen Skenario UAT — LapangIn</h1>
                    <div class="brand-subtitle">Platform Terintegrasi Reservasi &amp; Manajemen Venue Olahraga Multi-Court</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                    <span style="background: #eaf7d8; color: #416900; font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 8pt; border: 1px solid #acf847;">
                        VERSI 2.0 — STAGING READY
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Lingkungan Staging</td>
            <td><strong>https://lapangin.australiaeast.cloudapp.azure.com</strong></td>
            <td class="label">Protokol Keamanan</td>
            <td>HTTPS / TLS 1.3 (Let's Encrypt, HTTP/2, CSP &amp; Camera Policy)</td>
        </tr>
        <tr>
            <td class="label">Sprint / Tahapan</td>
            <td>Sprint 5 &amp; Deployment Final</td>
            <td class="label">Tanggal Dokumen</td>
            <td>28 Oktober 2026</td>
        </tr>
        <tr>
            <td class="label">Disusun Oleh</td>
            <td>Tim Pengembang LapangIn PBL</td>
            <td class="label">Hasil Automated Test</td>
            <td><strong style="color: #166534;">134 Tests Passed (100% Lulus, 614 Assertions)</strong></td>
        </tr>
    </table>

    <h2>1. Lingkup Pengujian &amp; Acceptance Criteria</h2>
    <p>Dokumen User Acceptance Testing (UAT) ini menguji kelayakan sistem dalam operasional nyata (staging production-grade) yang mencakup seluruh peran pengguna:</p>
    <ul>
        <li><strong>Akses Aman Berstandar Web:</strong> Seluruh modul berjalan di bawah HTTPS staging dengan Nginx security headers, Content Security Policy yang mengizinkan Alpine.js expressions dan Midtrans Snap SDK, serta <code>Permissions-Policy: camera=(self)</code> untuk scanner QR.</li>
        <li><strong>Aturan Pemesanan Online Wajib Akun:</strong> Pelanggan pada antarmuka web wajib memiliki akun terdaftar; tamu yang mencoba memesan otomatis diarahkan mendaftar dengan penyimpanan <em>intended URL</em> sehingga tidak kehilangan jadwal pilihan setelah login/register.</li>
        <li><strong>Guest Checkout Khusus Kasir (Walk-in):</strong> Tamu langsung di venue dilayani kasir dengan otomatisasi <em>shadow account</em> via nomor WhatsApp. Bila tamu sudah memiliki akun di web, reservasi kasir otomatis terhubung tanpa duplikasi data. Saat tamu mendaftar resmi di kemudian hari, sistem melakukan <em>auto-claim</em> seluruh riwayat booking lampau.</li>
        <li><strong>Proteksi Slot Masa Lalu:</strong> Slot jam pada hari H yang telah melewati jam saat ini otomatis berstatus <strong>"Lewat Jam"</strong> dan tidak dapat dipilih.</li>
        <li><strong>Modul Perlengkapan &amp; Skema Pembayaran:</strong> Penambahan rompi/bola dengan kalkulasi instan, serta opsi pembayaran DP 50% (pelunasan kasir) vs Lunas 100%.</li>
    </ul>

    <h2>2. Matriks Akun Pengujian Staging</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Role</th>
                <th style="width: 30%;">Email Login</th>
                <th style="width: 20%;">Kata Sandi</th>
                <th>Keterangan Hak Akses</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Administrator</strong></td>
                <td><code>admin@lapangin.staging</code></td>
                <td><code>Admin@123!</code></td>
                <td>Akses penuh dashboard analitik, tarif master, tarif khusus, akun kasir, dan export laporan PDF.</td>
            </tr>
            <tr>
                <td><strong>Kasir Venue</strong></td>
                <td><code>kasir@lapangin.staging</code></td>
                <td><code>Kasir@123!</code></td>
                <td>Akses operasional POS meja resepsionis, scanner QR kamera, token manual, walk-in order, dan approval reschedule.</td>
            </tr>
            <tr>
                <td><strong>Pelanggan 1</strong></td>
                <td><code>dimas@lapangin.staging</code></td>
                <td><code>Pelanggan@123!</code></td>
                <td>Pengujian alur booking online, modul perlengkapan, pembayaran DP/Lunas Midtrans, dan pengajuan reschedule.</td>
            </tr>
            <tr>
                <td><strong>Pelanggan 2</strong></td>
                <td><code>budi@lapangin.staging</code></td>
                <td><code>Pelanggan@123!</code></td>
                <td>Pengujian skenario konflik jadwal (anti double-booking) dan auto-claim nomor WhatsApp saat registrasi resmi.</td>
            </tr>
            <tr>
                <td><strong>Tamu Walk-In</strong></td>
                <td><em>(Tanpa Akun Awal)</em></td>
                <td><em>(Auto-Provision)</em></td>
                <td>Pengujian walk-in kasir berbasis nomor WA <code>081288889999</code> dan verifikasi auto-claim setelah registrasi susulan.</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2>3. Skenario Pengujian per Role</h2>

    <h3>A. Role: Pelanggan (Online Web)</h3>

    <h4>UAT-PENG-01: Autentikasi Pengguna &amp; Masking Kata Sandi</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Buka halaman <code>/login</code> di browser</td>
                <td>-</td>
                <td>Tampil single-card auth terpadu dengan tab Masuk ke Akun dan Daftar Akun Baru.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Ketik kata sandi di field password</td>
                <td><code>Pelanggan@123!</code></td>
                <td>Karakter sandi tersembunyi (masked) secara default untuk menjaga privasi pengguna.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Klik ikon mata (toggle intip kata sandi)</td>
                <td>-</td>
                <td>Karakter kata sandi terlihat jelas; diklik ulang menyembunyikan sandi kembali.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Pindah ke tab "Daftar Akun Baru"</td>
                <td>-</td>
                <td>Form berganti mulus tanpa reload halaman dan tanpa tabrakan layout visual.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Isi No. WhatsApp berawalan <code>+62</code> atau <code>08</code></td>
                <td><code>081234567890</code></td>
                <td>Nomor dinormalisasi otomatis ke format standar Indonesia dan akun berhasil terdaftar.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h4>UAT-PENG-02: Eksplorasi Katalog &amp; Tampilan Bersih Detail Lapangan</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Buka menu Katalog Lapangan</td>
                <td>-</td>
                <td>Menampilkan kartu seluruh lapangan dengan tarif weekday dan weekend yang tersinkronisasi.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Klik filter kategori olahraga (Badminton/Futsal)</td>
                <td>Kategori Cabor</td>
                <td>Daftar lapangan langsung menyaring kartu yang sesuai dengan kategori terpilih.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Periksa input tanggal katalog</td>
                <td>Tanggal kemarin</td>
                <td>Tanggal sebelum hari ini terkunci (min date = hari ini), mencegah pemilihan masa lampau.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Klik salah satu arena untuk melihat detail</td>
                <td>Lapangan 1 (Vinyl)</td>
                <td>Header tampil bersih (cluster switcher lapangan dihilangkan) dengan tombol <strong>"&larr; Kembali ke Katalog"</strong>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Pilih tanggal tanding via Carousel 7 Hari</td>
                <td>Tanggal +2 hari</td>
                <td>Jadwal ketersediaan slot jam langsung diperbarui sesuai tanggal yang dipilih.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h4>UAT-PENG-03: Validasi Otomatis Slot Jam Masa Lalu (Minor Fix Hari Ini)</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Pilih tanggal <strong>Hari Ini</strong> di carousel</td>
                <td><code>now()->toDateString()</code></td>
                <td>Slot jam terkelompokkan ke Sesi Siang (13:00-17:00) dan Malam Prime Time (18:00-23:00).</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Periksa slot jam yang sudah lewat dari jam sekarang</td>
                <td>Jam mulai &le; jam saat ini</td>
                <td>Slot berstatus <strong>"Lewat Jam"</strong>, ikon jam, teks tercoret, dan <strong>tidak dapat diklik</strong>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Periksa slot jam masa depan untuk hari ini</td>
                <td>Jam mulai &gt; jam saat ini</td>
                <td>Slot berstatus <strong>"Tersedia"</strong> dengan warna hijau dan dapat diklik untuk dipilih.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h4>UAT-PENG-04: Kebijakan Pemesanan Online Wajib Akun (Intended Redirect)</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Akses website dalam keadaan belum login (Guest)</td>
                <td>Browser Incognito</td>
                <td>Tamu dapat melihat lapangan, tanggal, dan memilih slot jam yang diinginkan.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Periksa kotak ringkasan pemesanan</td>
                <td>-</td>
                <td>Tampil notifikasi info: <em>"Pemesanan online memerlukan akun terdaftar. Anda akan diarahkan ke pendaftaran..."</em>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Klik tombol "Lanjut ke Pembayaran"</td>
                <td>-</td>
                <td>Sistem mengalihkan tamu ke <code>/register</code> dengan pesan pemberitahuan di atas form pendaftaran.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Lakukan pendaftaran atau login dengan akun yang ada</td>
                <td>Akun Pelanggan</td>
                <td>Sistem otomatis mengembalikan pengguna (intended redirect) langsung ke <code>/checkout</code> dengan jadwal yang sama.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <h4>UAT-PENG-05: Modul Perlengkapan, Pembayaran Midtrans Snap &amp; E-Tiket</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Tambah sewa perlengkapan di modul perlengkapan</td>
                <td>+2 Rompi, +1 Bola</td>
                <td>Total biaya sewa bertambah otomatis dengan kalkulasi biaya perlengkapan.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Pilih skema <strong>Bayar DP 50%</strong></td>
                <td>DP 50% (Rekomendasi)</td>
                <td>Rincian menampilkan 50% online sekarang dan 50% pelunasan di kasir venue.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Klik tombol Bayar DP</td>
                <td>-</td>
                <td>Pop-up Midtrans Snap terbuka dengan nominal DP yang tepat. Timer 10 menit aktif.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Selesaikan pembayaran sandbox (BCA VA / QRIS)</td>
                <td>Simulator Midtrans</td>
                <td>Webhook diterima, sistem mengarahkan ke halaman Sukses E-Tiket publik.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Periksa kelengkapan E-Tiket yang terbit</td>
                <td>-</td>
                <td>Memuat QR Code unik, Token Manual 6-Karakter, status <code>dp_terverifikasi</code>, dan rincian pelunasan.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h4>UAT-PENG-06: Reschedule Mandiri &amp; Pembatalan dengan Denda Penalti</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Buka Riwayat Booking untuk tiket H-3</td>
                <td>Jadwal &gt; 24 jam</td>
                <td>Tombol "Ajukan Reschedule" dan "Batalkan Booking" tampil aktif.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Pilih tanggal baru (+5 hari) dan kirim permohonan</td>
                <td>Alasan: Cuaca</td>
                <td>Permohonan reschedule berstatus <code>diajukan</code> dan menunggu persetujuan kasir.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Uji pembatalan dengan jadwal kickoff &gt; 24 jam</td>
                <td>Pembatalan H-3</td>
                <td>Sistem memotong penalti pembatalan 10%; status berubah menjadi <code>dibatalkan</code>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Uji pembatalan dengan jadwal kickoff &lt; 24 jam</td>
                <td>Pembatalan H &lt; 24 jam</td>
                <td>Sistem menampilkan peringatan penalti 50% (DP Hangus).</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h3>B. Role: Kasir (POS &amp; Check-In Onsite)</h3>

    <h4>UAT-KASIR-01: Scanner QR Code Kamera &amp; Turnstile Check-In</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Login kasir, buka menu <em>Scan QR Tiket</em></td>
                <td><code>kasir@lapangin.staging</code></td>
                <td>Browser mengaktifkan kamera berkat header <code>Permissions-Policy: camera=(self)</code> tanpa pesan error izin.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Arahkan kamera ke QR Code E-Tiket Pelanggan</td>
                <td>QR Code E-Tiket</td>
                <td>Kamera membaca instan; detail nama penyewa, nomor WhatsApp, dan arena langsung tampil.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Klik "Konfirmasi Check-in"</td>
                <td>-</td>
                <td>Tiket tervalidasi, stempel waktu check-in (WIB) tercatat di database.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Scan ulang tiket yang sama</td>
                <td>Tiket sudah check-in</td>
                <td>Sistem menolak dengan pesan peringatan: <em>"Tiket telah melakukan check-in sebelumnya"</em>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Scan tiket yang telah dibatalkan</td>
                <td>Tiket dibatalkan</td>
                <td>Sistem menolak dengan banner merah: <em>"Tiket tidak valid / telah dibatalkan"</em>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h4>UAT-KASIR-02: Input Token Manual 6-Karakter &amp; Kalkulator Pelunasan</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Buka tab Check-in &amp; Validasi, masukkan token manual</td>
                <td>Token 6 karakter (cth: <code>A1B2C3</code>)</td>
                <td>Data reservasi penyewa langsung ditemukan dan dimuat di layar kasir.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Periksa modul pelunasan untuk tiket DP 50%</td>
                <td>Sisa Tagihan: Rp 90.000</td>
                <td>Form pelunasan kasir terbuka dengan pilihan Tunai (Cash) atau QRIS.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Pilih Tunai dan masukkan jumlah uang diterima</td>
                <td>Diterima: Rp 100.000</td>
                <td>Kalkulator kasir otomatis menghitung uang kembalian: Rp 10.000 secara real-time.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Klik "Validasi Check-in &amp; Pelunasan"</td>
                <td>-</td>
                <td>Status pembayaran menjadi <code>lunas_kasir</code> dan check-in sukses terekam.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <h4>UAT-KASIR-03: Walk-in Order &amp; Auto-Provisioning Akun Tamu</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Buka menu Jadwal &amp; Walk-in di kasir</td>
                <td>-</td>
                <td>Tampil form walk-in order meja resepsionis dan kalender ketersediaan arena.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Input pemesanan walk-in untuk tamu baru (belum punya akun)</td>
                <td>Nama: Tamu Walkin, WA: <code>081288889999</code></td>
                <td>Sistem otomatis membuatkan <em>shadow account</em> berbasis nomor WhatsApp tanpa memerlukan password manual.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Input pemesanan walk-in untuk tamu yang <strong>sudah punya akun</strong> di web</td>
                <td>No. WA: <code>081233334444</code> (Akun Dimas)</td>
                <td>Sistem mencocokkan nomor telepon dan otomatis menghubungkan pemesanan ke akun Dimas tanpa membuat data duplikat.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Tamu dari langkah 2 mendaftar resmi di website dengan nomor sama</td>
                <td>No. WA: <code>081288889999</code></td>
                <td>Setelah registrasi, buka menu Riwayat Booking: Seluruh tiket walk-in lampau otomatis terklaim (auto-claim)!</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h4>UAT-KASIR-04: Approval Pengajuan Reschedule</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Buka menu Approval Reschedule di kasir</td>
                <td>-</td>
                <td>Menampilkan daftar permohonan reschedule yang diajukan pelanggan online.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Klik "Setujui Permintaan"</td>
                <td>Permohonan UAT-PENG-06</td>
                <td>Jadwal lama dipindahkan ke waktu baru, status disetujui, dan E-Tiket pelanggan ter-update.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h3>C. Role: Venue Manager / Administrator</h3>

    <h4>UAT-ADMIN-01: Monitoring Pendapatan &amp; Ekspor Rekonsiliasi PDF</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Langkah Pengujian</th>
                <th style="width: 22%;">Data Uji</th>
                <th style="width: 32%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Login admin, buka menu Laporan Rekonsiliasi</td>
                <td><code>admin@lapangin.staging</code></td>
                <td>Tabel rekapitulasi transaksi online vs kasir tampil rapi dengan filter rentang tanggal.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Klik tombol "Export PDF"</td>
                <td>Bulan Berjalan</td>
                <td>Mengunduh file PDF resmi laporan rekonsiliasi finansial berstempel venue dan kolom tanda tangan penanggung jawab.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Kelola tarif khusus pada menu Tarif Khusus</td>
                <td>Tarif Libur Nasional</td>
                <td>Tarif baru tersimpan dan otomatis menjadi dasar kalkulasi harga saat pelanggan memilih tanggal libur tersebut.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h2>4. Skenario Edge Case, Keamanan &amp; Validasi Waktu</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Kode ID</th>
                <th style="width: 30%;">Skenario Pengujian</th>
                <th style="width: 26%;">Kondisi &amp; Input</th>
                <th style="width: 28%;">Hasil yang Diharapkan</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>SEC-01</strong></td>
                <td>Proteksi Hak Akses Role Endpoint</td>
                <td>Pelanggan membuka <code>/admin</code> atau <code>/kasir</code></td>
                <td>Diblokir dengan status HTTP 403 Forbidden atau dialihkan ke halaman login.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td><strong>SEC-02</strong></td>
                <td>Proteksi Slot Masa Lampau via URL</td>
                <td>GET <code>/checkout?jam_mulai=08:00</code> (di jam 14:00)</td>
                <td>Ditolak oleh controller dengan status 422 (Jam sewa sudah lewat).</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td><strong>SEC-03</strong></td>
                <td>Anti Concurrency Double Booking</td>
                <td>2 user checkout slot yang sama pada detik yang sama</td>
                <td>Sistem mengunci baris slot; pesanan kedua gagal dan diberi info slot terisi.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td><strong>SEC-04</strong></td>
                <td>Auto-Cancel Timeout 10 Menit</td>
                <td>Pesanan tidak dibayar dalam 10 menit</td>
                <td>Job membatalkan jadwal otomatis, status menjadi <code>dibatalkan</code>, slot lepas.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td><strong>SEC-05</strong></td>
                <td>Validasi Signature Webhook Midtrans</td>
                <td>POST webhook dengan hash signature tidak cocok</td>
                <td>Ditolak dengan status 400/403; status database tidak berubah.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
            <tr>
                <td><strong>SEC-06</strong></td>
                <td>Standarisasi Zona Waktu Transaksi</td>
                <td>Pencatatan waktu booking, cancel, check-in</td>
                <td>Seluruh stempel waktu tercatat konsisten dalam format <strong>WIB (GMT+7)</strong>.</td>
                <td class="checkbox-cell">[ ]</td>
            </tr>
        </tbody>
    </table>

    <h2>5. Checklist Kesiapan Staging &amp; Demo Dosen</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Area Kesiapan</th>
                <th style="width: 65%;">Item Verifikasi</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Infrastruktur Azure VPS</strong></td>
                <td>Domain <code>lapangin.australiaeast.cloudapp.azure.com</code> aktif via HTTPS (Let's Encrypt), Docker container Nginx, App PHP 8.4-FPM, dan PostgreSQL healthy. Firewall UFW aktif port 22, 80, 443.</td>
                <td class="checkbox-cell" style="color: #166534; font-weight: bold;">[x] LULUS</td>
            </tr>
            <tr>
                <td><strong>Konfigurasi Keamanan Nginx</strong></td>
                <td>Content Security Policy (CSP) mengizinkan script 'self' &amp; Midtrans SDK, <code>Permissions-Policy: camera=(self)</code> aktif untuk scanner turnstile kasir.</td>
                <td class="checkbox-cell" style="color: #166534; font-weight: bold;">[x] LULUS</td>
            </tr>
            <tr>
                <td><strong>Aplikasi &amp; Database</strong></td>
                <td>Database PostgreSQL termigrasi penuh, master tarif &amp; seeder lapangan aktif, asset Vite terkompilasi (public/build), cache config &amp; view teroptimasi.</td>
                <td class="checkbox-cell" style="color: #166534; font-weight: bold;">[x] LULUS</td>
            </tr>
            <tr>
                <td><strong>Midtrans Sandbox</strong></td>
                <td>Midtrans Server &amp; Client Key aktif, Webhook notification URL terdaftar dan merespon 200 OK untuk skema bayar DP 50% dan Lunas 100%.</td>
                <td class="checkbox-cell" style="color: #166534; font-weight: bold;">[x] LULUS</td>
            </tr>
            <tr>
                <td><strong>Integritas Pengujian Otomatis</strong></td>
                <td>Seluruh test suite automated unit &amp; feature test lulus 100% (<strong>134 tests, 614 assertions, 0 failures</strong>).</td>
                <td class="checkbox-cell" style="color: #166534; font-weight: bold;">[x] LULUS</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen Pengujian LapangIn &bull; Hak Cipta &copy; 2026 Tim LapangIn PBL &bull; Generated on 28 Oktober 2026
    </div>

</body>
</html>
HTML;

$pdf = Pdf::loadHTML($html);
$pdf->setPaper('A4', 'portrait');
$pdf->save(__DIR__ . '/../docs/UAT_Scenario_LapangIn.pdf');

echo "SUCCESS: docs/UAT_Scenario_LapangIn.pdf updated (" . filesize(__DIR__ . '/../docs/UAT_Scenario_LapangIn.pdf') . " bytes)\n";
