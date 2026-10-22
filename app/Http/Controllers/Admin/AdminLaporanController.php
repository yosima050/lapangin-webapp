<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminLaporanController extends Controller
{
    /**
     * Tampilkan halaman laporan analitik dan rekonsiliasi finansial.
     */
    public function index(Request $request): View
    {
        $laporanData = $this->buildLaporanData($request);

        $transaksis = $laporanData['transaksiQuery']
            ->with(['jadwal.pelanggan', 'jadwal.lapangan'])
            ->orderByDesc('dibayar_pada')
            ->paginate(15)
            ->appends($request->query());

        return view('Admin.laporan.index', array_merge($laporanData, [
            'transaksiTerbaru' => $transaksis,
            'isPdf' => false,
        ]));
    }

    /**
     * Unduh berkas laporan rekonsiliasi kas dalam format PDF standar A4.
     */
    public function exportPdf(Request $request): Response
    {
        $laporanData = $this->buildLaporanData($request);

        $semuaTransaksi = $laporanData['transaksiQuery']
            ->with(['jadwal.pelanggan', 'jadwal.lapangan'])
            ->orderByDesc('dibayar_pada')
            ->get();

        $pdfData = array_merge($laporanData, [
            'transaksis' => $semuaTransaksi,
            'isPdf' => true,
            'dicetakPada' => now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB',
            'dicetakOleh' => auth()->user()->nama ?? auth()->user()->name ?? 'Administrator',
        ]);

        $pdf = Pdf::loadView('Admin.laporan.cetak_pdf', $pdfData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $safePeriode = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $laporanData['periode']);
        $filename = 'Laporan_Rekonsiliasi_LapangIn_' . $safePeriode . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Hitung agregasi rekonsiliasi kas dan susun filter transaksi.
     *
     * @return array<string, mixed>
     */
    protected function buildLaporanData(Request $request): array
    {
        $periode = $request->input('periode', 'bulan_ini');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');
        $metodeFilter = $request->input('metode', 'all');

        $now = now()->timezone('Asia/Jakarta');

        switch ($periode) {
            case 'hari_ini':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $periodeLabel = 'Hari Ini (' . $now->translatedFormat('d F Y') . ')';
                break;

            case 'minggu_ini':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $periodeLabel = 'Minggu Ini (' . $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M Y') . ')';
                break;

            case 'custom':
                if ($startDateInput && $endDateInput) {
                    $startDate = Carbon::parse($startDateInput, 'Asia/Jakarta')->startOfDay();
                    $endDate = Carbon::parse($endDateInput, 'Asia/Jakarta')->endOfDay();
                    $periodeLabel = Carbon::parse($startDateInput)->translatedFormat('d M Y') . ' s.d. ' . Carbon::parse($endDateInput)->translatedFormat('d M Y');
                } else {
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                    $periodeLabel = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                    $periode = 'bulan_ini';
                }
                break;

            case 'semua':
                $startDate = Carbon::create(2020, 1, 1)->startOfDay();
                $endDate = $now->copy()->addYear()->endOfDay();
                $periodeLabel = 'Seluruh Riwayat Transaksi';
                break;

            case 'bulan_ini':
            default:
                $periode = 'bulan_ini';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $periodeLabel = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                break;
        }

        // Query dasar transaksi sukses
        $baseQuery = Transaksi::query()
            ->whereIn('status', ['berhasil', 'settlement'])
            ->whereBetween('dibayar_pada', [$startDate, $endDate]);

        // 1. Rekonsiliasi Kas Keseluruhan pada Periode Terpilih
        $totalPendapatan = (float) (clone $baseQuery)->sum('jumlah');
        $totalTransaksiSukses = (clone $baseQuery)->count();

        // 2. Pendapatan Online (Payment Gateway Midtrans)
        $pendapatanOnline = (float) (clone $baseQuery)
            ->where('metode', 'online')
            ->sum('jumlah');
        $transaksiOnlineCount = (clone $baseQuery)
            ->where('metode', 'online')
            ->count();

        // 3. Penerimaan Kas Fisik di Meja Kasir (Tunai / Cash)
        $pendapatanTunai = (float) (clone $baseQuery)
            ->whereIn('metode', ['tunai', 'cash'])
            ->sum('jumlah');
        $transaksiTunaiCount = (clone $baseQuery)
            ->whereIn('metode', ['tunai', 'cash'])
            ->count();

        // Rekapitulasi per Lapangan
        $lapanganOmset = Lapangan::withCount(['jadwals as total_booking' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
              ->whereIn('status', ['terverifikasi', 'lunas', 'selesai']);
        }])->get();

        // Query transaksi yang difilter untuk tabel dan ekspor PDF
        $filteredQuery = clone $baseQuery;
        if ($metodeFilter !== 'all' && in_array($metodeFilter, ['online', 'tunai'], true)) {
            $filteredQuery->where('metode', $metodeFilter);
        }

        return [
            'periode' => $periode,
            'periodeLabel' => $periodeLabel,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'metodeFilter' => $metodeFilter,
            'totalPendapatan' => $totalPendapatan,
            'pendapatanOnline' => $pendapatanOnline,
            'pendapatanTunai' => $pendapatanTunai,
            'totalTransaksiSukses' => $totalTransaksiSukses,
            'transaksiOnlineCount' => $transaksiOnlineCount,
            'transaksiTunaiCount' => $transaksiTunaiCount,
            'lapanganOmset' => $lapanganOmset,
            'transaksiQuery' => $filteredQuery,
        ];
    }
}
