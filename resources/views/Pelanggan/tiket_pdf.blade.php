<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket LapangIn - {{ $tokenManual ?? 'TICKET' }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 7mm 10mm 7mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #0b1c30;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Utility Helpers */
        .w-full { width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Header / Kop Tiket */
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #1a146b;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .brand-title {
            font-size: 18pt;
            font-weight: 900;
            color: #1a146b;
            margin: 0;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .brand-subtitle {
            font-size: 7pt;
            font-weight: 700;
            color: #416900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
        }

        .header-badge {
            background-color: #1a146b;
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: 800;
            display: inline-block;
            margin-bottom: 3px;
        }

        .header-meta {
            font-size: 7pt;
            color: #64748b;
            line-height: 1.25;
        }

        /* Banner Status Konfirmasi */
        .status-banner {
            width: 100%;
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 5px 10px;
            margin-bottom: 8px;
        }

        .status-title {
            font-size: 8.5pt;
            font-weight: 800;
            color: #065f46;
            margin: 0;
        }

        .status-desc {
            font-size: 7pt;
            color: #047857;
            margin: 1px 0 0 0;
        }

        /* Boarding Pass Box Utama */
        .ticket-box {
            width: 100%;
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .ticket-header-table {
            width: 100%;
            background-color: #1a146b;
            color: #ffffff;
            padding: 6px 10px;
        }

        .ticket-header-title {
            font-size: 12pt;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
        }

        .ticket-header-sub {
            font-size: 7pt;
            color: #c7d2fe;
            margin-top: 1px;
        }

        .code-box {
            text-align: right;
        }

        .code-label {
            font-size: 6pt;
            font-weight: 700;
            color: #c7d2fe;
            text-transform: uppercase;
        }

        .code-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13pt;
            font-weight: 900;
            color: #acf847;
            letter-spacing: 2px;
            line-height: 1;
        }

        /* Dua Kolom Isi Tiket */
        .body-table {
            width: 100%;
            border-collapse: collapse;
        }

        .left-col {
            width: 66%;
            padding: 8px 12px;
            vertical-align: top;
            border-right: 1.5px dashed #cbd5e1;
        }

        .right-col {
            width: 34%;
            padding: 8px 10px;
            vertical-align: top;
            background-color: #f8fafc;
            text-align: center;
        }

        /* Info Pertandingan */
        .section-title {
            font-size: 6.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 4px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 2px;
        }

        .info-grid-table {
            width: 100%;
            margin-bottom: 6px;
        }

        .info-cell {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 4px 7px;
            vertical-align: top;
        }

        .info-label {
            font-size: 6pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin: 0;
        }

        .info-value {
            font-size: 8pt;
            font-weight: 800;
            color: #0f172a;
            margin: 1px 0 0 0;
        }

        .info-value.highlight {
            color: #1a146b;
        }

        /* Rincian Finansial */
        .finance-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 4px 7px;
            margin-top: 4px;
        }

        .finance-row td {
            padding: 1.5px 0;
            font-size: 7pt;
        }

        .finance-total td {
            padding-top: 3px;
            border-top: 1px solid #cbd5e1;
            font-weight: 800;
            font-size: 7.5pt;
        }

        .badge-lunas {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: 800;
            font-size: 6.5pt;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        .badge-dp {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: 800;
            font-size: 6.5pt;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        /* Kolom Kanan: Gate Turnstile & QR */
        .qr-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 5px;
            display: inline-block;
            margin-bottom: 4px;
        }

        .qr-label {
            font-size: 6.5pt;
            font-weight: 800;
            color: #1a146b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }

        .qr-help {
            font-size: 6pt;
            color: #64748b;
            margin: 1px 0 4px 0;
        }

        .token-box {
            background-color: #ffffff;
            border: 1.5px solid #1a146b;
            border-radius: 5px;
            padding: 4px 5px;
            margin-top: 2px;
        }

        .token-label {
            font-size: 5.5pt;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }

        .token-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12pt;
            font-weight: 900;
            color: #1a146b;
            letter-spacing: 2px;
            margin-top: 1px;
            line-height: 1;
        }

        /* Tabel Perlengkapan (jika ada) */
        .addon-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7pt;
        }

        .addon-table th {
            background-color: #f1f5f9;
            color: #334155;
            padding: 3px 5px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
        }

        .addon-table td {
            padding: 3px 5px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* 3 Box Aturan & Protokol */
        .rules-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin-bottom: 6px;
        }

        .rule-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 5px 7px;
            vertical-align: top;
            width: 33.33%;
        }

        .rule-tag {
            font-size: 5.5pt;
            font-weight: 800;
            color: #416900;
            text-transform: uppercase;
        }

        .rule-title {
            font-size: 7pt;
            font-weight: 800;
            color: #0f172a;
            margin: 1px 0 1px 0;
        }

        .rule-desc {
            font-size: 6pt;
            color: #64748b;
            line-height: 1.2;
            margin: 0;
        }

        /* Venue Location Box */
        .location-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 5px 8px;
            margin-bottom: 6px;
        }

        .location-title {
            font-size: 6.5pt;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
        }

        .location-text {
            font-size: 6.5pt;
            color: #334155;
            margin: 1px 0 0 0;
        }

        /* Footer Tiket */
        .footer-table {
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 6pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- 1. Header / Kop Tiket --}}
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <div class="brand-title">LapangIn</div>
                <div class="brand-subtitle">Sistem Reservasi &amp; Gelanggang Olahraga Modern</div>
            </td>
            <td style="width: 45%; vertical-align: middle; text-align: right;">
                <div class="header-badge">E-TIKET RESMI &bull; OFFICIAL MATCH PASS</div>
                <div class="header-meta">
                    Dokumen ID: <strong style="color: #1a146b;">{{ $jadwal->token_manual ?: substr($jadwal->id, 0, 8) }}</strong><br>
                    Dicetak pada: {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </td>
        </tr>
    </table>

    {{-- 2. Banner Status Konfirmasi --}}
    <div class="status-banner">
        <table style="width: 100%;">
            <tr>
                <td style="width: 72%; vertical-align: middle;">
                    <div class="status-title">&bull; RESERVASI TERVERIFIKASI &mdash; AKSES MASUK AKTIF</div>
                    <div class="status-desc">Tunjukkan barcode / QR code ini langsung ke petugas resepsionis saat tim tiba di venue.</div>
                </td>
                <td style="width: 28%; vertical-align: middle; text-align: right;">
                    @if($isLunas)
                        <span class="badge-lunas">LUNAS 100% ONLINE</span>
                    @else
                        <span class="badge-dp">DP 50% TERVERIFIKASI</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- 3. Boarding Pass Card Utama --}}
    <div class="ticket-box">
        {{-- Card Header --}}
        <table class="ticket-header-table">
            <tr>
                <td style="width: 70%; vertical-align: middle;">
                    <div class="ticket-header-title">{{ $jadwal->lapangan?->nama ?? 'Arena Pertandingan LapangIn' }}</div>
                    <div class="ticket-header-sub">
                        Kategori: <strong>{{ $jadwal->lapangan?->kategori ?? 'Olahraga' }}</strong>
                        @if($jadwal->lapangan?->tipe_lantai)
                            &bull; Lantai: <strong>{{ $jadwal->lapangan->tipe_lantai }}</strong>
                        @endif
                    </div>
                </td>
                <td style="width: 30%; vertical-align: middle; text-align: right;">
                    <div class="code-label">KODE RESERVASI</div>
                    <div class="code-value">{{ $tokenManual }}</div>
                </td>
            </tr>
        </table>

        {{-- Card Body --}}
        <table class="body-table">
            <tr>
                {{-- Kolom Kiri: Rincian Pertandingan & Finansial --}}
                <td class="left-col">
                    <div class="section-title">INFORMASI PERTANDINGAN &amp; PEMESAN</div>

                    <table class="info-grid-table">
                        <tr>
                            <td class="info-cell" style="width: 50%;">
                                <div class="info-label">TANGGAL PERTANDINGAN</div>
                                <div class="info-value highlight">
                                    {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}
                                </div>
                            </td>
                            <td style="width: 3%;"></td>
                            <td class="info-cell" style="width: 47%;">
                                <div class="info-label">WAKTU KICK-OFF</div>
                                <div class="info-value highlight">
                                    {{ substr((string) $jadwal->jam_mulai, 0, 5) }} &ndash; {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB
                                </div>
                            </td>
                        </tr>
                        <tr><td colspan="3" style="height: 4px;"></td></tr>
                        <tr>
                            <td class="info-cell">
                                <div class="info-label">PEMESAN RESMI</div>
                                <div class="info-value">{{ $namaPemesan }}</div>
                                <div style="font-size: 6pt; color: #64748b; margin-top: 1px;">WA: {{ $noHpPemesan ?: '-' }}</div>
                            </td>
                            <td></td>
                            <td class="info-cell">
                                <div class="info-label">TIM / KOMUNITAS</div>
                                <div class="info-value">{{ $jadwal->nama_tim ?? 'Pemain Umum' }}</div>
                                <div style="font-size: 6pt; color: #64748b; margin-top: 1px;">Status: Terdaftar</div>
                            </td>
                        </tr>
                    </table>

                    <div class="section-title" style="margin-top: 5px;">RINCIAN PEMBAYARAN</div>

                    <table class="finance-table">
                        <tr class="finance-row">
                            <td style="width: 60%; color: #475569;">Biaya Sewa Lapangan</td>
                            <td style="width: 40%; text-align: right; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($biayaSewa, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if($biayaLayanan > 0)
                        <tr class="finance-row">
                            <td style="color: #475569;">Biaya Layanan Platform</td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($biayaLayanan, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        @if($totalPerlengkapan > 0)
                        <tr class="finance-row">
                            <td style="color: #475569;">Sewa Perlengkapan Tambahan</td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($totalPerlengkapan, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        <tr class="finance-row">
                            <td style="color: #047857; font-weight: 700;">Terbayar Online</td>
                            <td style="text-align: right; font-weight: 800; color: #047857;">
                                Rp {{ number_format($nominalTerbayar, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="finance-total">
                            <td style="color: #1a146b;">Total Tagihan Reservasi</td>
                            <td style="text-align: right; color: #1a146b;">
                                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 3px;">
                                @if($sisaTagihan > 0)
                                    <table style="width: 100%; background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 4px; padding: 3px 5px;">
                                        <tr>
                                            <td style="font-size: 6.5pt; font-weight: 800; color: #92400e;">
                                                SISA BAYAR DI MEJA KASIR:
                                            </td>
                                            <td style="text-align: right; font-size: 7.5pt; font-weight: 900; color: #b45309;">
                                                Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div style="font-size: 6.5pt; font-weight: 700; color: #065f46; padding-top: 1px;">
                                        &check; Seluruh biaya telah lunas. Tidak ada tagihan tersisa di kasir.
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Kolom Kanan: QR Code & Check-in Token --}}
                <td class="right-col">
                    <div class="qr-label">GATE CHECK-IN</div>

                    <div class="qr-card">
                        <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="115" height="115" alt="QR Tiket" style="display: block; margin: 0 auto;">
                    </div>

                    <div class="qr-help">Arahkan QR ke scanner petugas kasir</div>

                    <div class="token-box">
                        <div class="token-label">TOKEN MANUAL KASIR</div>
                        <div class="token-text">{{ $tokenManual }}</div>
                    </div>

                    <div style="font-size: 6pt; color: #64748b; margin-top: 4px; line-height: 1.2;">
                        Gunakan token manual bila kamera scanner kasir mengalami kendala.
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 4. Tabel Perlengkapan Tambahan (jika ada pesanan) --}}
    @if(isset($jadwal->detailSewaPerlengkapans) && $jadwal->detailSewaPerlengkapans->count() > 0)
        <div style="font-size: 6.5pt; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 2px;">
            RINCIAN SEWA PERLENGKAPAN TAMBAHAN
        </div>
        <table class="addon-table">
            <thead>
                <tr>
                    <th>Item Perlengkapan</th>
                    <th style="text-align: center; width: 15%;">Jumlah</th>
                    <th style="text-align: right; width: 25%;">Harga Satuan</th>
                    <th style="text-align: right; width: 25%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwal->detailSewaPerlengkapans as $addon)
                    <tr>
                        <td><strong>{{ $addon->nama_perlengkapan }}</strong></td>
                        <td style="text-align: center;">{{ $addon->jumlah }} unit</td>
                        <td style="text-align: right;">Rp {{ number_format($addon->harga_satuan, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($addon->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- 5. Panduan & Tata Tertib Pertandingan --}}
    <table class="rules-table">
        <tr>
            <td class="rule-card">
                <div class="rule-tag">ATURAN 01</div>
                <div class="rule-title">Hadir 15 Menit Awal</div>
                <p class="rule-desc">Lakukan registrasi dan pemanasan tim sebelum jam kick-off dimulai di venue.</p>
            </td>
            <td class="rule-card">
                <div class="rule-tag">ATURAN 02</div>
                <div class="rule-title">Sepatu Sol Karet (IC)</div>
                <p class="rule-desc">Wajib sepatu sol karet indoor non-marking. Dilarang keras memakai pul outdoor.</p>
            </td>
            <td class="rule-card">
                <div class="rule-tag">ATURAN 03</div>
                <div class="rule-title">Simpan E-Tiket</div>
                <p class="rule-desc">Tunjukkan e-tiket kepada petugas keamanan untuk akses parkir dan fasilitas arena.</p>
            </td>
        </tr>
    </table>

    {{-- 6. Lokasi Gelanggang --}}
    @if($jadwal->lapangan?->alamat || $jadwal->lapangan?->lokasi)
        <div class="location-box">
            <div class="location-title">LOKASI &amp; ALAMAT ARENA PERTANDINGAN:</div>
            <div class="location-text">
                {{ $jadwal->lapangan?->nama }} &mdash; {{ $jadwal->lapangan?->alamat ?? $jadwal->lapangan?->lokasi }}
            </div>
        </div>
    @endif

    {{-- 7. Footer / Autentikasi Sistem --}}
    <table class="footer-table">
        <tr>
            <td style="width: 70%;">
                Tiket digital ini diterbitkan secara sah oleh platform <strong>LapangIn</strong> dan dilindungi hak cipta.<br>
                Segala bentuk manipulasi atau pemalsuan kode tiket akan ditolak oleh sistem audit turnstile kasir.
            </td>
            <td style="width: 30%; text-align: right;">
                Sistem ID: <strong>{{ strtoupper(substr(md5($jadwal->id . ($jadwal->kode_qr ?? '')), 0, 10)) }}</strong><br>
                &copy; {{ date('Y') }} LapangIn. Hak Cipta Dilindungi.
            </td>
        </tr>
    </table>

</body>
</html>
