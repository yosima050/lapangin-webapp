<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekonsiliasi Finansial - LapangIn</title>
    <style>
        @page {
            margin: 1.5cm 1.2cm 1.5cm 1.2cm;
            size: a4 portrait;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header / Kop Laporan */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1a146b;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-logo {
            font-size: 20pt;
            font-weight: bold;
            color: #1a146b;
            letter-spacing: -0.5px;
        }

        .header-tagline {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .header-meta {
            text-align: right;
            font-size: 8.5pt;
            color: #475569;
        }

        .report-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            color: #0b1c30;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 12px 0 4px 0;
        }

        .report-subtitle {
            text-align: center;
            font-size: 9.5pt;
            color: #475569;
            margin-bottom: 18px;
        }

        /* Rekonsiliasi Cards / Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 20px;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: left;
        }

        .summary-card.highlight {
            background-color: #eff4ff;
            border-color: #bfdbfe;
        }

        .summary-card.online {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }

        .summary-card.cash {
            background-color: #fefce8;
            border-color: #fef08a;
        }

        .card-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .card-value {
            font-size: 12pt;
            font-weight: bold;
            color: #1e293b;
        }

        .summary-card.highlight .card-value {
            color: #1a146b;
        }

        .summary-card.online .card-value {
            color: #15803d;
        }

        .summary-card.cash .card-value {
            color: #a16207;
        }

        .card-desc {
            font-size: 7pt;
            color: #64748b;
            margin-top: 3px;
        }

        /* Section Headings */
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            margin: 16px 0 8px 0;
            border-left: 3px solid #1a146b;
            padding-left: 6px;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 8.5pt;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 7px 8px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .badge-online {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-tunai {
            background-color: #fef3c7;
            color: #b45309;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }

        /* Footer & Signatures */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            font-size: 8.5pt;
            color: #334155;
        }

        .signature-space {
            height: 50px;
        }

        .signature-line {
            display: inline-block;
            width: 170px;
            border-bottom: 1px solid #334155;
            margin-bottom: 4px;
        }

        .footer-note {
            margin-top: 25px;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    {{-- Header Kop Dokumen --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="header-logo">LapangIn</div>
                <div class="header-tagline">Sistem Manajemen &amp; Reservasi Venue Olahraga Terpadu</div>
            </td>
            <td class="header-meta" style="vertical-align: top;">
                <strong>Dokumen Resmi Rekonsiliasi Keuangan</strong><br>
                Tanggal Cetak: {{ $dicetakPada }}<br>
                Petugas Cetak: {{ $dicetakOleh }}
            </td>
        </tr>
    </table>

    <div class="report-title">Rekapitulasi Omset &amp; Rekonsiliasi Kas</div>
    <div class="report-subtitle">Periode Laporan: <strong>{{ $periodeLabel }}</strong></div>

    {{-- Kotak Ringkasan Rekonsiliasi Kas --}}
    <table class="summary-table">
        <tr>
            <td style="width: 33.3%;">
                <div class="summary-card highlight">
                    <div class="card-label">Total Omset Sukses</div>
                    <div class="card-value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                    <div class="card-desc">{{ $totalTransaksiSukses }} Transaksi Terverifikasi</div>
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-card online">
                    <div class="card-label">Omset Online (Midtrans)</div>
                    <div class="card-value">Rp {{ number_format($pendapatanOnline, 0, ',', '.') }}</div>
                    <div class="card-desc">{{ $transaksiOnlineCount }} Transaksi (Snap / QRIS / VA)</div>
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-card cash">
                    <div class="card-label">Kas Fisik Kasir (Tunai)</div>
                    <div class="card-value">Rp {{ number_format($pendapatanTunai, 0, ',', '.') }}</div>
                    <div class="card-desc">{{ $transaksiTunaiCount }} Transaksi (Meja POS Kasir)</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan per Lapangan --}}
    @if(isset($lapanganOmset) && $lapanganOmset->isNotEmpty())
        <div class="section-title">Aktivitas Reservasi per Lapangan (Periode Terpilih)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Nama Lapangan</th>
                    <th style="width: 25%;">Kategori Cabang Olahraga</th>
                    <th style="width: 25%;" class="text-right">Total Jam / Sesi Booking</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lapanganOmset as $idx => $lap)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $lap->nama }}</strong></td>
                        <td>{{ $lap->kategori ?? 'Umum' }}</td>
                        <td class="text-right"><strong>{{ $lap->total_booking ?? 0 }} Sesi</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Tabel Rincian Transaksi --}}
    <div class="section-title">Rincian Transaksi Keuangan Terverifikasi</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Waktu Bayar</th>
                <th style="width: 20%;">Referensi / Order ID</th>
                <th style="width: 22%;">Pelanggan / Lapangan</th>
                <th style="width: 12%;">Metode</th>
                <th style="width: 11%;">Status</th>
                <th style="width: 15%;" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $index => $tx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $tx->dibayar_pada ? $tx->dibayar_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 7.5pt;">{{ $tx->order_id ?? substr($tx->id, 0, 13) }}</span>
                    </td>
                    <td>
                        <strong>{{ $tx->jadwal?->pelanggan?->nama ?? $tx->jadwal?->nama_tamu ?? 'Walk-in Guest' }}</strong><br>
                        <span style="font-size: 7pt; color: #64748b;">{{ $tx->jadwal?->lapangan?->nama ?? 'Lapangan' }}</span>
                    </td>
                    <td>
                        <span class="badge {{ in_array(strtolower((string)$tx->metode), ['tunai', 'cash']) ? 'badge-tunai' : 'badge-online' }}">
                            {{ strtoupper($tx->metode ?? 'Online') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-success">
                            {{ $tx->status }}
                        </span>
                    </td>
                    <td class="text-right font-bold" style="font-weight: bold; color: #0f172a;">
                        Rp {{ number_format($tx->jumlah, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 16px; color: #64748b;">
                        Tidak ada transaksi keuangan pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="6" class="text-right" style="padding: 8px; border-top: 1px solid #cbd5e1;">
                    TOTAL OMSET TERREKONSILIASI:
                </td>
                <td class="text-right" style="padding: 8px; border-top: 1px solid #cbd5e1; color: #1a146b; font-size: 9.5pt;">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Tanda Tangan / Verifikasi --}}
    <table class="signature-table">
        <tr>
            <td>
                Dipersiapkan Oleh,<br>
                <strong>Staf Kasir / Operasional</strong>
                <div class="signature-space"></div>
                <span class="signature-line"></span><br>
                ( {{ $dicetakOleh }} )
            </td>
            <td>
                Mengetahui &amp; Menyetujui,<br>
                <strong>Manajer / Pengelola Venue</strong>
                <div class="signature-space"></div>
                <span class="signature-line"></span><br>
                ( Manajer Operasional LapangIn )
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh Sistem Informasi Reservasi <strong>LapangIn</strong> sebagai bukti sah audit rekonsiliasi kas internal.
    </div>
</body>
</html>
