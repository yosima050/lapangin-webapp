# Dokumen Skenario UAT — LapangIn
**Platform Reservasi & Manajemen Venue Olahraga Multi-Court Terintegrasi**

- **Versi Dokumen:** 2.1 (Custom Domain Production-Ready)
- **Sprint:** 5 & Deployment Final
- **Tanggal:** 29 Oktober 2026
- **Lingkungan Pengujian (Production / Staging):** `https://lapangin.biz.id`
- **Penyusun:** Tim Pengembang LapangIn PBL

---

## Daftar Isi
1. [Lingkup Pengujian & Baseline Arsitektur](#1-lingkup-pengujian--baseline-arsitektur)
2. [Matriks Kredensial Akun Pengujian](#2-matriks-kredensial-akun-pengujian)
3. [Skenario Pengujian per Role](#3-skenario-pengujian-per-role)
   - [A. Role: Pelanggan (Online Web)](#a-role-pelanggan-online-web)
   - [B. Role: Kasir (POS & Check-In Onsite)](#b-role-kasir-pos--check-in-onsite)
   - [C. Role: Venue Manager / Administrator](#c-role-venue-manager--administrator)
4. [Skenario Edge Case, Keamanan & Validasi Waktu](#4-skenario-edge-case-keamanan--validasi-waktu)
5. [Checklist Kesiapan Staging & Demo Dosen](#5-checklist-kesiapan-staging--demo-dosen)

---

## 1. Lingkup Pengujian & Baseline Arsitektur

Dokumen ini mencakup skenario **User Acceptance Testing (UAT)** menyeluruh untuk memverifikasi fungsionalitas, keamanan, dan keandalan sistem aplikasi LapangIn pada infrastruktur Staging Azure Cloud VPS dengan protokol HTTPS penuh.

### Acceptance Criteria Utama Sistem
- ✅ **Akses Aman HTTPS:** Akses domain staging menggunakan sertifikat SSL Let's Encrypt aktif dengan HTTP/2.
- ✅ **Nginx Security Headers:** Content Security Policy (CSP) dan Permissions-Policy Camera (`camera=(self)`) aktif dan mengizinkan scanner QR browser serta Midtrans Snap SDK.
- ✅ **Pemesanan Online Wajib Akun:** Pemesanan online pada website mengharuskan pengguna memiliki akun terdaftar; tamu tanpa login otomatis diarahkan mendaftar dengan penyimpanan state pemesanan (*intended URL*).
- ✅ **Guest Checkout Khusus Kasir:** Tamu *walk-in* tanpa akun dilayani langsung di kasir venue dengan *auto-provision shadow account*, yang otomatis terklaim (*auto-claim*) saat tamu mendaftar akun resmi.
- ✅ **Validasi Slot Waktu Masa Lalu:** Slot jam untuk hari ini yang telah lewat dari waktu sekarang otomatis dinonaktifkan (*disabled*, status "Lewat Jam").
- ✅ **Katalog & Modul Perlengkapan:** Filter kategori, carousel tanggal 7 hari, stepper modul sewa perlengkapan (rompi, bola), dan pilihan skema pembayaran DP 50% vs Lunas 100%.
- ✅ **Turnstile & Check-In Onsite:** Validasi QR Code via kamera kasir, token manual 6-karakter, dan kalkulator pelunasan onsite instan.
- ✅ **Integritas Pengujian Otomatis:** Seluruh 134 pengujian unit/fitur (`php artisan test`) lulus 100% tanpa kegagalan.

---

## 2. Matriks Kredensial Akun Pengujian

> [!IMPORTANT]
> Gunakan kredensial berikut untuk melakukan pengujian di lingkungan Staging. Pastikan database telah dimigrasikan dan diisi data seeder.

| Role Pengguna | Alamat Email | Kata Sandi | Deskripsi & Hak Akses |
|---|---|---|---|
| **Administrator** | `admin@lapangin.staging` | `Admin@123!` | Akses penuh dashboard analitik, tarif master, tarif khusus, akun kasir, dan cetak PDF rekapitulasi. |
| **Kasir Venue** | `kasir@lapangin.staging` | `Kasir@123!` | Akses operasional POS meja resepsionis, scanner kamera QR code, token manual, walk-in order, dan approval reschedule. |
| **Pelanggan Utama** | `dimas@lapangin.staging` | `Pelanggan@123!` | Akun terdaftar aktif untuk pengujian alur booking online, penambahan perlengkapan, pembayaran DP/lunas, dan reschedule. |
| **Pelanggan Sekunder** | `budi@lapangin.staging` | `Pelanggan@123!` | Akun terdaftar pengujian skenario konflik jadwal (double booking prevention) dan auto-claim nomor WhatsApp. |
| **Tamu Walk-In (Guest)** | *(Tanpa Akun Awal)* | *(Auto-Provision)* | Pengujian alur guest walk-in kasir dengan No. WhatsApp `081288889999` dan klaim riwayat saat registrasi susulan. |

---

## 3. Skenario Pengujian per Role

### A. Role: Pelanggan (Online Web)

#### UAT-PENG-01: Autentikasi Pengguna & Tampilan Sandi
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka halaman autentikasi `https://lapangin.biz.id/login` | - | Tampil single-card auth dengan tab *Masuk ke Akun* dan *Daftar Akun Baru*. | [ ] |
| 2 | Masukkan kata sandi pada form login | `Pelanggan@123!` | Karakter kata sandi tersembunyi (*masked bullet* secara default). | [ ] |
| 3 | Klik ikon mata (toggle intip sandi) | - | Karakter kata sandi terlihat jelas, klik ulang menyembunyikan sandi kembali. | [ ] |
| 4 | Klik tab "Daftar Akun Baru" | - | Berpindah ke form registrasi secara mulus tanpa tumpang tindih visual. | [ ] |
| 5 | Isi form registrasi dengan No. WhatsApp berawalan `08` atau `+62` | `081234567890` | Nomor dinormalisasi otomatis ke format standar dan registrasi berhasil. | [ ] |

#### UAT-PENG-02: Eksplorasi Katalog & Pemilihan Lapangan
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka menu *Jelajahi Lapangan* / Katalog | - | Menampilkan daftar lapangan lengkap dengan foto, badge tarif weekday/weekend, dan fasilitas. | [ ] |
| 2 | Klik tombol filter kategori cabang olahraga | Badminton / Futsal | Katalog langsung memfilter lapangan sesuai kategori yang dipilih. | [ ] |
| 3 | Periksa pemilih tanggal katalog | Tanggal kemarin | Tanggal sebelum hari ini dinonaktifkan (`min="hari ini"`), tidak bisa dipilih. | [ ] |
| 4 | Klik salah satu kartu lapangan | Lapangan 1 (Vinyl) | Masuk ke halaman detail lapangan dengan tampilan 2 kolom modern. | [ ] |
| 5 | Periksa header kartu detail lapangan | - | Header bersih dan lapang (cluster switcher lapangan tidak ada), terdapat tombol **"← Kembali ke Katalog"**. | [ ] |

#### UAT-PENG-03: Deteksi Slot Waktu Masa Lalu (Minor Fix Jam Hari Ini)
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Di halaman detail lapangan, pilih tanggal **Hari Ini** | `now()->toDateString()` | Menampilkan pengelompokan Sesi Siang/Sore (13:00-17:00) dan Malam Prime Time (18:00-23:00). | [ ] |
| 2 | Periksa slot jam yang telah lewat dari waktu jam saat ini | Cth: jam sekarang 20:00, periksa slot 14:00 atau 19:00 | Slot berstatus **"Lewat Jam"**, berikon jam dinding, teks dicoret, dan **tidak dapat diklik / dipilih**. | [ ] |
| 3 | Periksa slot jam masa depan untuk hari ini | Cth: slot 21:00 atau 22:00 | Slot berstatus **"Tersedia"** dengan indikator hijau dan dapat diklik. | [ ] |
| 4 | Klik tanggal besok pada carousel 7 hari | Besok (`+1 hari`) | Seluruh jam operasional dari pagi hingga malam berstatus "Tersedia". | [ ] |

#### UAT-PENG-04: Kebijakan Pemesanan Online Wajib Akun (Intended Redirect)
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka website dalam mode *Incognito* / belum login | Mode Tamu (Guest) | Header menampilkan tombol "Masuk / Daftar Akun". | [ ] |
| 2 | Pilih lapangan, tanggal besok, dan slot jam yang tersedia | 2 Jam Sesi | Ringkasan pemesanan menghitung tarif durasi secara real-time. | [ ] |
| 3 | Periksa banner edukatif di atas tombol booking | - | Tampil pesan: *"Pemesanan online memerlukan akun terdaftar. Anda akan diarahkan ke pendaftaran akun..."*. | [ ] |
| 4 | Klik tombol "Lanjut ke Pemesanan" | - | Sistem mengarahkan tamu ke halaman pendaftaran (`register`) dengan pesan info instruktif. | [ ] |
| 5 | Selesaikan registrasi atau login pada tab Masuk | Akun baru / terdaftar | Sistem secara otomatis mengembalikan pengguna (*intended redirect*) langsung ke halaman checkout dengan rincian jadwal yang tetap utuh. | [ ] |

#### UAT-PENG-05: Modul Perlengkapan & Pembayaran Midtrans Snap (DP vs Lunas)
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Di halaman detail/checkout, tentukan perlengkapan tambahan | +2 Rompi Tanding, +1 Bola FIFA | Total harga bertambah otomatis sesuai harga sewa perlengkapan. | [ ] |
| 2 | Pilih skema pembayaran **Bayar DP 50%** | DP 50% (Rekomendasi) | Rincian menampilkan nominal DP yang harus dibayar sekarang dan sisa 50% yang dibayar di kasir venue. | [ ] |
| 3 | Klik tombol bayar DP | - | Pop-up Midtrans Snap terbuka dengan total nominal tagihan DP. | [ ] |
| 4 | Lakukan pembayaran sandbox (Virtual Account / QRIS) | Simulator Midtrans | Pembayaran sukses, webhook diterima, halaman dialihkan ke halaman E-Tiket Sukses. | [ ] |
| 5 | Periksa E-Tiket yang diterbitkan | - | Menampilkan QR Code check-in, Token Manual 6-Karakter, status `dp_terverifikasi`, dan catatan sisa tagihan kasir. | [ ] |

#### UAT-PENG-06: Reschedule & Pembatalan dengan Penalti
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka menu *Riwayat Booking*, klik tiket yang memiliki jadwal > 24 jam | Booking H-3 | Tombol "Ajukan Reschedule" dan "Batalkan Booking" aktif. | [ ] |
| 2 | Klik "Ajukan Reschedule" | Tanggal baru (+5 hari) | Sistem menampilkan slot kosong untuk tanggal pengganti; pengajuan terkirim dengan status `diajukan`. | [ ] |
| 3 | Uji pembatalan dengan jadwal kickoff > 24 jam | Pembatalan H > 24 Jam | Tampil konfirmasi penalti 10%; setelah dikonfirmasi status menjadi `dibatalkan` dengan denda 10%. | [ ] |
| 4 | Uji pembatalan dengan jadwal kickoff < 24 jam | Pembatalan H < 24 Jam | Sistem menampilkan peringatan penalti 50% (DP Hangus). | [ ] |

---

### B. Role: Kasir (POS & Check-In Onsite)

#### UAT-KASIR-01: Scanner QR Code Check-in via Kamera Browser
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Login sebagai Kasir (`kasir@lapangin.staging`) | - | Masuk ke antarmuka terminal meja kasir venue. | [ ] |
| 2 | Buka menu *Scan QR Tiket* via HTTPS | - | Browser meminta izin akses kamera; setelah diizinkan, video stream kamera aktif tanpa error CSP/Permissions. | [ ] |
| 3 | Arahkan kamera ke QR Code E-Tiket Pelanggan | QR Code E-Tiket | Kamera memindai instan, data pesanan dan identitas penyewa muncul di layar. | [ ] |
| 4 | Klik tombol "Konfirmasi Check-in" | - | Tiket tervalidasi, stempel waktu check-in (WIB) tersimpan di database. | [ ] |
| 5 | Lakukan scan ulang pada tiket yang sama | Tiket sudah check-in | Sistem menolak dengan pesan peringatan: *"Tiket telah melakukan check-in sebelumnya"*. | [ ] |
| 6 | Lakukan scan pada tiket yang statusnya dibatalkan | Tiket dibatalkan | Sistem menolak dengan banner merah: *"Tiket tidak valid / telah dibatalkan"*. | [ ] |

#### UAT-KASIR-02: Validasi Token Manual & Pelunasan Onsite
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka menu *Check-in & Validasi* kasir | - | Menampilkan pencarian antrean hari ini dan form input token manual. | [ ] |
| 2 | Masukkan 6 karakter token manual e-tiket pelanggan | Cth: `A1B2C3` | Data reservasi yang cocok langsung ditampilkan. | [ ] |
| 3 | Jika tiket berstatus DP 50%, periksa modul pelunasan kasir | Sisa: Rp 90.000 | Tampil form pelunasan dengan metode Tunai / QRIS. | [ ] |
| 4 | Pilih Tunai, ketik uang diterima | Diterima: Rp 100.000 | Kalkulator otomatis menghitung kembalian: Rp 10.000. | [ ] |
| 5 | Klik "Validasi Check-in & Pelunasan" | - | Status pembayaran diperbarui menjadi `lunas_kasir`, transaksi selesai tercatat. | [ ] |

#### UAT-KASIR-03: Pemesanan Walk-In & Auto-Provision Akun Tamu
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Di meja kasir, buka menu *Jadwal & Walk-in* | - | Tampil matriks ketersediaan slot hari ini dan form walk-in order. | [ ] |
| 2 | Input walk-in untuk tamu yang **belum memiliki akun** | Nama: Budi Santoso, WA: `081288889999` | Kasir memilih slot dan skema pembayaran tunai. | [ ] |
| 3 | Simpan reservasi walk-in | - | Transaksi berhasil, jadwal terverifikasi, dan sistem otomatis membuatkan *shadow account* untuk nomor tersebut. | [ ] |
| 4 | Input walk-in untuk tamu yang **sudah punya akun di web** | No. WA: `081233334444` (Akun Dimas) | Kasir menyimpan walk-in. Sistem otomatis mencocokkan nomor dan menyambungkan booking ke akun Dimas tanpa membuat user baru. | [ ] |

#### UAT-KASIR-04: Auto-Claim Riwayat Pemesanan oleh Tamu Walk-In
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Tamu walk-in membuka website LapangIn di kemudian hari | Tamu dari UAT-KASIR-03 | Tamu mengakses menu pendaftaran (`/register`). | [ ] |
| 2 | Tamu mendaftar akun resmi menggunakan nomor WhatsApp yang sama | No. WA: `081288889999` | Registrasi berhasil, akun resmi baru aktif. | [ ] |
| 3 | Tamu membuka menu *Riwayat Booking* di akun barunya | - | Seluruh riwayat reservasi dan e-tiket yang pernah dibuatkan oleh kasir otomatis terhubung dan tampil di daftar booking! | [ ] |

#### UAT-KASIR-05: Review Pengajuan Reschedule Jadwal
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka menu *Approval Reschedule* di panel kasir | - | Menampilkan daftar permohonan reschedule yang diajukan pelanggan. | [ ] |
| 2 | Periksa rincian: jadwal lama vs usulan jadwal baru | - | Rincian lapangan, tanggal pengganti, dan alasan tampil lengkap. | [ ] |
| 3 | Klik tombol "Setujui Permintaan" | - | Jadwal lama dipindahkan ke slot baru, status berubah menjadi `disetujui`, dan e-tiket pelanggan otomatis diperbarui. | [ ] |

---

### C. Role: Venue Manager / Administrator

#### UAT-ADMIN-01: Dashboard Analitik & Monitoring Okupansi
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Login sebagai Administrator (`admin@lapangin.staging`) | - | Dashboard admin menampilkan metrik total pendapatan, tingkat okupansi slot, dan jumlah reservasi aktif. | [ ] |
| 2 | Periksa grafik tren pendapatan per lapangan | - | Grafik menampilkan visualisasi performa seluruh lapangan (futsal, badminton, mini soccer). | [ ] |

#### UAT-ADMIN-02: Laporan Rekonsiliasi Finansial & Cetak PDF
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka menu *Laporan Rekonsiliasi* | - | Tabel rekapitulasi transaksi online vs kasir tampil terstruktur. | [ ] |
| 2 | Filter laporan berdasarkan rentang tanggal dan metode bayar | Bulan Berjalan | Data tabel memperbarui angka subtotal dan total pendapatan bersih. | [ ] |
| 3 | Klik tombol "Export Laporan PDF" | - | Mengunduh berkas PDF resmi rekapitulasi pendapatan lengkap dengan kop surat dan kolom tanda tangan penanggung jawab venue. | [ ] |

#### UAT-ADMIN-03: Konfigurasi Tarif Khusus & Jadwal Operasional
| No | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
|:--:|---|---|---|:--:|
| 1 | Buka menu *Tarif Khusus* | - | Tampil daftar kebijakan harga per jam untuk tanggal/event tertentu. | [ ] |
| 2 | Tambah tarif khusus untuk hari libur nasional mendatang | Tanggal Libur, Tarif: Rp 220.000 | Tarif tersimpan dan otomatis menjadi acuan kalkulasi harga saat pelanggan memilih tanggal tersebut. | [ ] |
| 3 | Buka menu *Kelola Lapangan* | - | Admin dapat mengubah jam operasional, foto lapangan, dan inventaris perlengkapan tambahan. | [ ] |

---

## 4. Skenario Edge Case, Keamanan & Validasi Waktu

| Kode ID | Skenario Pengujian Edge Case | Kondisi & Tindakan | Hasil yang Diharapkan | Status |
|:---:|---|---|---|:---:|
| **SEC-01** | Akses unauthorized endpoint kasir & admin | Pelanggan/tamu mencoba membuka URL `/admin/*` atau `/kasir/*` | Sistem memblokir dengan status **HTTP 403 Forbidden** atau mengalihkan ke login. | [ ] |
| **SEC-02** | Pemesanan slot masa lalu melalui manipulasi URL | Request GET `/checkout?jam_mulai=08:00` pada hari ini pukul 14:00 | Sistem memvalidasi dan menolak dengan status **422 Unprocessable Content** (*Jam sewa sudah lewat*). | [ ] |
| **SEC-03** | Pembayaran slot masa lalu via API payload | Request POST `/checkout/pay` dengan jam mulai di masa lalu | Ditolak dengan respon JSON error 422 (*Jam sewa sudah lewat dari waktu saat ini*). | [ ] |
| **SEC-04** | Concurrency / Double Booking Prevention | Dua pengguna memesan slot dan lapangan yang sama pada detik bersamaan | Hanya satu pesanan yang berhasil; pesanan kedua dibatalkan dengan notifikasi slot telah terisi. | [ ] |
| **SEC-05** | Auto-Cancel Pending Booking Timeout | Pesanan dibuat namun tidak dibayar dalam 10 menit | Background job membatalkan jadwal otomatis (`status = dibatalkan`), slot kembali tersedia untuk orang lain. | [ ] |
| **SEC-06** | Validasi Keamanan Signature Midtrans Webhook | Notifikasi webhook palsu tanpa signature key Midtrans yang valid | Ditolak oleh server dengan kode HTTP 403/400, status pembayaran tidak berubah. | [ ] |
| **SEC-07** | Keamanan Permissions Policy Kamera Nginx | Pemuatan scanner kasir pada HTTPS staging | Header Nginx `Permissions-Policy: camera=(self)` aktif; kamera dapat diakses tanpa hambatan browser policy. | [ ] |
| **SEC-08** | Standarisasi Zona Waktu Transaksi & Check-in | Pencatatan log transaksi, denda pembatalan, dan stempel check-in | Seluruh stempel waktu konsisten tercatat dalam format **WIB (Asia/Jakarta, GMT+7)**. | [ ] |

---

## 5. Checklist Kesiapan Staging & Demo Dosen

### A. Infrastruktur & Keamanan Server Azure VPS
- [x] Host Staging aktif di Azure Cloud VPS (`lapangin.biz.id`).
- [x] Docker Container Nginx, PHP 8.4-FPM, dan PostgreSQL berjalan stabil (*healthy*).
- [x] Sertifikat SSL Let's Encrypt aktif dan diperbarui otomatis via Certbot.
- [x] Konfigurasi Content Security Policy (CSP) dan Permissions Policy Camera teruji aktif.
- [x] UFW Firewall membatasi akses publik hanya pada port 22 (SSH), 80 (HTTP), dan 443 (HTTPS).

### B. Aplikasi & Database
- [x] Environment `.env` terkonfigurasi dengan `APP_ENV=production` dan `APP_DEBUG=false`.
- [x] Kunci enkripsi aplikasi `APP_KEY` terkonfigurasi.
- [x] Migrasi tabel database dan master data lapangan telah dijalankan.
- [x] Seluruh aset frontend terkompilasi optimal via Vite production build (`public/build`).
- [x] Seluruh cache aplikasi dioptimasi (`php artisan optimize`).

### C. Pembayaran & Pengujian
- [x] Midtrans Server Key & Client Key Sandbox terhubung dengan kredensial aktif.
- [x] Midtrans Webhook Notification URL terdaftar dan merespon 200 OK.
- [x] Seluruh test suite pengujian otomatis (`php artisan test`) lulus 100% (**134 tests, 614 assertions, 0 failures**).
- [x] Skenario UAT siap diperagakan secara live untuk penilaian dosen dan penguji.
