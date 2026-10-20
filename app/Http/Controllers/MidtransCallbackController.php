<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransCallbackController extends Controller
{
    /**
     * Handle incoming HTTP notification/webhook from Midtrans Payment Gateway.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Log setiap notifikasi yang masuk
        Log::info('Midtrans Notification:', $request->all());

        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $serverKey = (string) config('services.midtrans.server_key');
        $signatureKey = (string) $request->input('signature_key');

        // 2. Validasi SHA512 signature key
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans Callback: Invalid Signature Key.', [
                'order_id' => $orderId,
                'received' => $signatureKey,
                'expected' => $expectedSignature,
            ]);

            return response()->json([
                'message' => 'Invalid signature key',
            ], 403);
        }

        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');
        $paymentType = $request->input('payment_type');
        $transactionId = $request->input('transaction_id');

        // 3. Cari data transaksi di database
        $transaksi = Transaksi::where('order_id', $orderId)
            ->orWhere('referensi_midtrans', $orderId)
            ->orWhere('id', $orderId)
            ->first();

        if (!$transaksi) {
            Log::warning("Midtrans Callback: Transaksi dengan order_id {$orderId} tidak ditemukan di database.");

            return response()->json([
                'message' => 'Notification handled',
            ], 200);
        }

        // Simpan data metadata Midtrans
        $transaksi->payment_type = $paymentType ?? $transaksi->payment_type;
        $transaksi->transaction_status = $transactionStatus;
        $transaksi->midtrans_transaction_id = $transactionId ?? $transaksi->midtrans_transaction_id;
        if (empty($transaksi->order_id)) {
            $transaksi->order_id = $orderId;
        }

        // 4. Tangani status transaksi Midtrans
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $transaksi->status = 'pending';
            } elseif ($fraudStatus === 'accept') {
                $transaksi->status = 'berhasil';
                $transaksi->dibayar_pada = now();
            }
        } elseif ($transactionStatus === 'settlement') {
            $transaksi->status = 'berhasil';
            $transaksi->dibayar_pada = now();
        } elseif ($transactionStatus === 'pending') {
            $transaksi->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $transaksi->status = 'gagal';
        }

        $transaksi->save();

        // 5. Update status Jadwal terkait
        $jadwal = $transaksi->jadwal;
        if ($jadwal) {
            if ($transaksi->status === 'berhasil') {
                if ($transaksi->jenis === 'dp') {
                    $jadwal->status_pembayaran = 'dp_terverifikasi';
                    $jadwal->status = 'terverifikasi';
                } else {
                    $jadwal->status_pembayaran = 'lunas_online';
                    $jadwal->status = 'lunas';
                }

                if (empty($jadwal->kode_qr)) {
                    $jadwal->kode_qr = 'LPGN-' . date('Ymd') . '-' . rand(1000, 9999);
                }
                if (empty($jadwal->token_manual)) {
                    $jadwal->token_manual = strtoupper(Str::random(6));
                }

                $jadwal->save();
            } elseif ($transaksi->status === 'gagal') {
                if ($jadwal->status === 'pending') {
                    $jadwal->status = 'dibatalkan';
                    $jadwal->status_pembayaran = 'belum_bayar';
                    $jadwal->save();
                }
            }
        }

        Log::info("Midtrans Callback: Berhasil memproses order_id {$orderId} dengan status {$transactionStatus}.");

        // 6. Wajib mengembalikan respon HTTP 200 JSON
        return response()->json([
            'message' => 'Notification handled',
        ], 200);
    }
}
