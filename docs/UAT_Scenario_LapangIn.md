# Dokumen Skenario UAT — LapangIn
**Versi:** 1.0 | **Sprint:** 5 | **Tanggal:** 27 Oktober 2026
**Prepared by:** Tim LapangIn PBL

---

## Daftar Isi
1. [Lingkup Pengujian](#lingkup-pengujian)
2. [Akun Pengujian](#akun-pengujian)
3. [Skenario per Role](#skenario-per-role)
4. [Edge Case & Keamanan](#edge-case--keamanan)
5. [Checklist Pre-Deploy](#checklist-pre-deploy)

---

## Lingkup Pengujian

Dokumen ini mencakup skenario **User Acceptance Testing (UAT)** untuk seluruh alur utama aplikasi LapangIn yang siap diuji di lingkungan staging (Cloud VPS dengan HTTPS aktif).

### Acceptance Criteria Utama
- ✅ Aplikasi dapat diakses via HTTPS dengan SSL valid
- ✅ Database PostgreSQL terhubung dan semua migrasi berjalan
- ✅ Webhook Midtrans Sandbox berhasil menembak URL domain staging
- ✅ Seluruh pengujian otomatis `php artisan test` lulus 100%
- ✅ Semua fitur berjalan sesuai skenario di dokumen ini

---

## Akun Pengujian

> [!IMPORTANT]
> Buat akun berikut di environment staging sebelum memulai UAT. Gunakan seeder atau buat manual via panel admin.

| Role | Email | Password | Keterangan |
|------|-------|----------|------------|
| Admin | `admin@lapangin.staging` | `Admin@123!` | Akses penuh sistem |
| Kasir | `kasir@lapangin.staging` | `Kasir@123!` | POS & Check-in |
| Pelanggan 1 | `andi@lapangin.staging` | `Pelanggan@123!` | Pengujian booking utama |
| Pelanggan 2 | `budi@lapangin.staging` | `Pelanggan@123!` | Pengujian konflik slot |

---

## Skenario per Role

### 👤 Role: Pelanggan

#### UAT-PENG-01: Registrasi & Login
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka `https://lapangin.example.com/register` | Halaman registrasi tampil dengan HTTPS |
| 2 | Isi nama, email baru, no. HP, password | Form tervalidasi |
| 3 | Submit registrasi | Redirect ke dashboard pelanggan |
| 4 | Logout, lalu login ulang | Login berhasil, sesi aktif |

#### UAT-PENG-02: Booking Online dengan Pembayaran DP
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Login sebagai Pelanggan 1 | Dashboard pelanggan tampil |
| 2 | Buka katalog lapangan | Daftar lapangan tampil dengan harga |
| 3 | Pilih lapangan, pilih tanggal +5 hari, pilih slot 10:00-11:00 | Ringkasan booking tampil |
| 4 | Pilih metode "DP Online via Midtrans" | Redirect ke Midtrans Snap |
| 5 | Selesaikan pembayaran sandbox (gunakan kartu test Midtrans) | Webhook diterima, status DP terverifikasi |
| 6 | Cek halaman riwayat booking | Status: `dp_terverifikasi` |
| 7 | Buka e-tiket | QR code dan token 6 karakter tampil |

#### UAT-PENG-03: Reschedule Jadwal (H-24 Jam)
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka detail booking dari UAT-PENG-02 | Tombol "Ajukan Reschedule" tampil |
| 2 | Klik "Ajukan Reschedule" | Halaman pilih jadwal pengganti tampil |
| 3 | Pilih tanggal +7 hari, pilih slot 14:00-15:00 | Slot ditampilkan sebagai "Tersedia" |
| 4 | Isi alasan, submit | Status reschedule: `diajukan`, menunggu kasir |
| 5 | Coba pilih slot hari ini atau besok pagi (< 24 jam) | Slot bertanda "Mepet (<24 Jam)", tidak bisa dipilih |

#### UAT-PENG-04: Pembatalan dengan Penalti
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka booking yang kickoff-nya > 24 jam | Tombol "Batalkan" tampil dengan info penalti 10% |
| 2 | Konfirmasi pembatalan | Status: `dibatalkan`, denda = 10% harga |
| 3 | Buat booking baru untuk kickoff < 24 jam dari sekarang | Booking berhasil |
| 4 | Batalkan booking tersebut | Info penalti: "Hangus DP (H < 24 Jam)", denda = 50% |

---

### 🖥️ Role: Kasir

#### UAT-KASIR-01: Scan QR Check-In
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Login sebagai Kasir | Dashboard kasir tampil |
| 2 | Buka halaman Scanner QR | Kamera browser aktif |
| 3 | Scan QR code dari e-tiket Pelanggan 1 | Identitas penyewa tampil, tombol Check-in aktif |
| 4 | Klik "Konfirmasi Check-in" | Tiket ter-stamped, waktu check-in tercatat WIB |
| 5 | Scan tiket yang sama kembali | Pesan: "Sudah check-in sebelumnya" |
| 6 | Scan tiket yang dibatalkan | Pesan error merah: tiket tidak valid / dibatalkan |

#### UAT-KASIR-02: Input Token Manual
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Di halaman scanner, gunakan input "Token Manual 6 Karakter" | Field input tampil |
| 2 | Masukkan token dari e-tiket | Data penyewa tampil |
| 3 | Masukkan token acak yang tidak ada | Error 422, pesan jelas |

#### UAT-KASIR-03: Walk-in Order (Booking Langsung)
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka halaman "Buat Walk-in" | Form booking kasir tampil |
| 2 | Pilih lapangan, tanggal +2 hari, slot 16:00-17:00 (kosong) | Slot tersedia dikonfirmasi |
| 3 | Isi nama penyewa, no. HP, metode bayar "Tunai" | Form valid |
| 4 | Submit | Booking dibuat, status `terverifikasi`, `lunas_kasir` |
| 5 | Coba buat walk-in di slot yang sama | Error: slot sudah terisi |

#### UAT-KASIR-04: Review Reschedule
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka tab "Pengajuan Reschedule" | Daftar pengajuan status `diajukan` tampil |
| 2 | Klik "Setujui" pada pengajuan dari UAT-PENG-03 | Jadwal berubah ke waktu baru, status `disetujui` |
| 3 | Buat pengajuan reschedule baru, lalu klik "Tolak" | Status `ditolak`, jadwal asli tidak berubah |

#### UAT-KASIR-05: Indikator Booking Dibatalkan
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka daftar jadwal di kasir | Booking yang dibatalkan tampil banner merah "DIBATALKAN" |
| 2 | Lihat detail booking dibatalkan | Form check-in/pembayaran dinonaktifkan (disabled) |

---

### 🔧 Role: Admin

#### UAT-ADMIN-01: Laporan Rekonsiliasi
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Login sebagai Admin | Dashboard admin tampil dengan statistik |
| 2 | Buka menu "Laporan Rekonsiliasi" | Tabel pendapatan per lapangan tampil |
| 3 | Filter berdasarkan rentang tanggal | Data terfilter sesuai |
| 4 | Klik "Export PDF" | File PDF terunduh dengan tanda tangan kepala venue |

#### UAT-ADMIN-02: Manajemen Tarif Khusus
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka menu "Tarif Khusus" | Daftar tarif khusus per lapangan tampil |
| 2 | Buat tarif khusus untuk lapangan tertentu pada tanggal tertentu | Tarif tersimpan |
| 3 | Pelanggan booking di tanggal tersebut | Harga menggunakan tarif khusus |

#### UAT-ADMIN-03: Manajemen Akun Kasir
| # | Langkah | Hasil yang Diharapkan |
|---|---------|----------------------|
| 1 | Buka menu "Akun Kasir" | Daftar akun kasir tampil |
| 2 | Tambah akun kasir baru | Akun tersimpan dengan role `kasir` |
| 3 | Login dengan akun kasir baru | Akses kasir berfungsi normal |

---

## Edge Case & Keamanan

| ID | Skenario | Hasil yang Diharapkan |
|----|----------|----------------------|
| SEC-01 | Akses `/admin` tanpa login | Redirect ke halaman login |
| SEC-02 | Pelanggan akses URL `/kasir/scan` | HTTP 403 Forbidden |
| SEC-03 | Kasir akses URL `/admin/laporan` | HTTP 403 Forbidden |
| SEC-04 | Pelanggan buka e-tiket milik orang lain via URL langsung | HTTP 403 Forbidden |
| SEC-05 | Request webhook Midtrans tanpa signature key valid | HTTP 403 / ditolak |
| SEC-06 | Double booking via dua tab browser bersamaan | Salah satu gagal, error 422 |
| SEC-07 | Upload file gambar lapangan > 10 MB | Error validasi ukuran file |
| TZ-01 | Cek waktu pembatalan tercatat di catatan kasir | Harus dalam format WIB (GMT+7) |
| TZ-02 | Cek waktu check-in tersimpan | Harus dalam format WIB (GMT+7) |
| MOB-01 | Akses e-tiket via smartphone | Layout responsif, QR code terbaca |
| MOB-02 | Akses halaman kasir via tablet | Layout kasir terbaca dan fungsional |

---

## Checklist Pre-Deploy

### Server & Infrastruktur
- [ ] VPS provisioned (Ubuntu 22.04 LTS, min. 2 vCPU, 4 GB RAM)
- [ ] Docker & Docker Compose terinstall
- [ ] Domain diarahkan ke IP VPS (A record DNS aktif)
- [ ] SSL Let's Encrypt berhasil diterbitkan (`certbot --nginx`)
- [ ] UFW firewall aktif: port 22, 80, 443 saja
- [ ] Fail2ban aktif untuk SSH brute-force protection

### Aplikasi
- [ ] `.env` di-populate dari `deploy/.env.staging` (semua `<GANTI_INI>` terisi)
- [ ] `php artisan key:generate` dijalankan
- [ ] `php artisan migrate --force` berjalan tanpa error
- [ ] `php artisan optimize` dijalankan (cache config/route/view)
- [ ] `storage/` writable oleh `www-data`
- [ ] `php artisan storage:link` dijalankan

### Integrasi
- [ ] Midtrans Notification URL diset ke `https://lapangin.example.com/midtrans/webhook`
- [ ] Test webhook manual dari dashboard Midtrans Sandbox → respons 200 OK
- [ ] Google OAuth redirect URI diperbarui ke domain staging

### Pengujian
- [ ] `php artisan test` — semua test pass (0 failures)
- [ ] Seluruh skenario UAT di dokumen ini telah dijalankan dan diceklis
- [ ] Demo dosen dapat dilakukan di URL staging yang aktif

