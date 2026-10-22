<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming Midtrans Webhook notification.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Logging transparan payload webhook
        Log::info('Midtrans Webhook Received:', $request->all());

        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $serverKey = (string) config('services.midtrans.server_key');
        $signatureKey = (string) $request->input('signature_key');

        // 2. Validasi Keamanan Signature SHA512
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans Webhook: Invalid signature key', [
                'order_id' => $orderId,
                'signature_received' => $signatureKey,
                'signature_expected' => $expectedSignature,
            ]);

            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status');
        $paymentType = $request->input('payment_type');
        $transactionId = $request->input('transaction_id');

        // 3. Cari data transaksi berdasarkan order_id
        $query = Transaksi::where('order_id', $orderId)
            ->orWhere('referensi_midtrans', $orderId);

        if (Str::isUuid($orderId)) {
            $query->orWhere('id', $orderId);
        }

        $transaksi = $query->first();

        if (! $transaksi) {
            Log::warning("Midtrans Webhook: Transaksi dengan order_id {$orderId} tidak ditemukan.");

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully',
            ], 200);
        }

        // Sinkronisasi metadata dari Midtrans
        if ($paymentType) {
            $transaksi->payment_type = $paymentType;
        }
        if ($transactionId) {
            $transaksi->midtrans_transaction_id = $transactionId;
        }
        $transaksi->transaction_status = $transactionStatus;

        $jadwal = $transaksi->jadwal;

        // 4. Penanganan Status Pembayaran
        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $transaksi->status = 'berhasil';
            $transaksi->dibayar_pada = now();
            $transaksi->save();

            if ($jadwal) {
                $jadwal->status_pembayaran = ($transaksi->jenis === 'full') ? 'lunas_online' : 'dp_terverifikasi';
                $jadwal->status = 'terverifikasi';

                // Generate Unique Token/String untuk kode_qr & token_manual
                if (empty($jadwal->kode_qr)) {
                    $jadwal->kode_qr = 'LPNG-QR-'.strtoupper(Str::random(12)).'-'.time();
                }

                if (
                    empty($jadwal->token_manual) ||
                    !preg_match('/^[A-Z0-9]{6}$/', $jadwal->token_manual)
                ) {
                    do {
                        $token = strtoupper(Str::random(6));
                    } while (
                        \App\Models\Jadwal::where('token_manual', $token)->where('id', '!=', $jadwal->id)->exists()
                    );

                    $jadwal->token_manual = $token;
                }

                $jadwal->save();
            }
        } elseif ($transactionStatus === 'pending') {
            $transaksi->status = 'pending';
            $transaksi->save();
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            $transaksi->status = 'gagal';
            $transaksi->save();

            if ($jadwal) {
                $jadwal->status_pembayaran = 'belum_bayar';
                $jadwal->status = 'dibatalkan';
                $jadwal->save();
            }
        } else {
            $transaksi->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
        ], 200);
    }
}
