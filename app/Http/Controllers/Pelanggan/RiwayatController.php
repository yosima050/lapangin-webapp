<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Transaksi;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        Jadwal::autoCancelExpiredPendingBookings();

        $pelanggan = $request->user();

        // Otomatis sinkronisasi transaksi yang masih pending ke Midtrans API
        if ($pelanggan && class_exists(PaymentService::class)) {
            $paymentService = app(PaymentService::class);
            $pendingTransaksis = Transaksi::whereHas('jadwal', function ($q) use ($pelanggan) {
                $q->where('pelanggan_id', $pelanggan->id);
            })->where('status', 'pending')
              ->whereNotNull('order_id')
              ->get();

            foreach ($pendingTransaksis as $trx) {
                $paymentService->syncStatus($trx);
            }
        }

        $jadwals = $pelanggan ? $pelanggan->jadwals()
            ->with(['lapangan', 'transaksis', 'reschedules'])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get() : collect();

        return view('Pelanggan.riwayat', compact('jadwals'));
    }

    /**
     * Unduh e-tiket resmi dalam format PDF standar A4 berkualitas tinggi.
     */
    public function downloadTiketPdf(Request $request, string $id)
    {
        $jadwal = Jadwal::with(['lapangan', 'pelanggan', 'transaksis', 'detailSewaPerlengkapans'])
            ->findOrFail($id);

        $user = $request->user();
        if ($user && $user->role === 'pelanggan' && $jadwal->pelanggan_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh e-tiket ini.');
        }

        $namaPemesan = $jadwal->nama_tamu ?: ($jadwal->pelanggan?->nama ?? 'Pelanggan LapangIn');
        $noHpPemesan = $jadwal->no_wa_tamu ?: ($jadwal->pelanggan?->no_hp ?? '-');
        $emailPemesan = $jadwal->email_tamu ?: ($jadwal->pelanggan?->email ?? '-');

        $biayaSewa = (float) ($jadwal->harga_disepakati ?? 0);
        $biayaLayanan = (float) ($jadwal->biaya_layanan ?? 0);
        $totalPerlengkapan = (float) $jadwal->detailSewaPerlengkapans->sum('subtotal');
        $totalTagihan = $biayaSewa + $biayaLayanan + $totalPerlengkapan;

        $nominalTerbayar = (float) $jadwal->transaksis->where('status', 'berhasil')->sum('jumlah');
        if ($nominalTerbayar <= 0) {
            $nominalTerbayar = str_contains(strtolower((string) $jadwal->status_pembayaran), 'dp')
                ? ($totalTagihan / 2)
                : $totalTagihan;
        }
        $sisaTagihan = max(0, $totalTagihan - $nominalTerbayar);
        $isLunas = ($sisaTagihan <= 0 || in_array($jadwal->status_pembayaran, ['lunas_online', 'lunas_kasir', 'lunas']));

        $qrPayload = $jadwal->kode_qr ?: ($jadwal->token_manual ?: ('LPNG-' . $jadwal->id));
        $qrSvg = (string) QrCode::size(115)->margin(0)->errorCorrection('M')->generate($qrPayload);
        $qrBase64 = base64_encode($qrSvg);
        $tokenManual = $jadwal->token_manual ?: strtoupper(substr($jadwal->id, 0, 6));

        $data = compact(
            'jadwal',
            'namaPemesan',
            'noHpPemesan',
            'emailPemesan',
            'biayaSewa',
            'biayaLayanan',
            'totalPerlengkapan',
            'totalTagihan',
            'nominalTerbayar',
            'sisaTagihan',
            'isLunas',
            'qrBase64',
            'tokenManual'
        );

        $pdf = Pdf::loadView('Pelanggan.tiket_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica',
            ]);

        $safeKode = preg_replace('/[^A-Za-z0-9_-]/', '_', $tokenManual);
        $filename = "E-Tiket_LapangIn_{$safeKode}.pdf";

        if ($request->query('stream') == '1') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
