<?php

namespace App\Services;

use App\Models\Transaksi;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    /**
     * Inisialisasi konfigurasi Midtrans SDK.
     */
    public function __construct()
    {
        $this->initMidtrans();
    }

    /**
     * Konfigurasi parameter Midtrans SDK dari konfigurasi aplikasi.
     */
    public function initMidtrans(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    /**
     * Buat Snap Token melalui Midtrans Snap API.
     *
     * @param  mixed  $customerDetails
     */
    public function createSnapToken(Transaksi $transaksi, $customerDetails, array $itemDetails = []): string
    {
        $this->initMidtrans();

        // Caching: gunakan snap_token yang sudah ada jika belum expired/terbayar
        if (! empty($transaksi->snap_token) && ($transaksi->status === 'pending' || empty($transaksi->status))) {
            return $transaksi->snap_token;
        }

        // Pastikan order_id unik terisi
        if (empty($transaksi->order_id)) {
            $transaksi->order_id = 'LPNG-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $transaksi->save();
        }

        $grossAmount = (int) round((float) $transaksi->jumlah);

        // Ekstraksi data pelanggan secara fleksibel (array atau model Pelanggan/User)
        $firstName = 'Pelanggan Lapangin';
        $email = 'customer@lapangin.id';
        $phone = '081234567890';

        if (is_array($customerDetails)) {
            $firstName = $customerDetails['first_name'] ?? $customerDetails['nama'] ?? $customerDetails['name'] ?? $firstName;
            $email = $customerDetails['email'] ?? $email;
            $phone = $customerDetails['phone'] ?? $customerDetails['no_hp'] ?? $customerDetails['no_telp'] ?? $phone;
        } elseif (is_object($customerDetails)) {
            $firstName = $customerDetails->nama ?? $customerDetails->name ?? $customerDetails->first_name ?? $firstName;
            $email = $customerDetails->email ?? $email;
            $phone = $customerDetails->no_hp ?? $customerDetails->no_telp ?? $customerDetails->phone ?? $phone;
        }

        // Siapkan item details default jika tidak diberikan
        if (empty($itemDetails)) {
            $namaLapangan = 'Sewa Lapangan';
            if ($transaksi->jadwal && $transaksi->jadwal->lapangan) {
                $namaLapangan = $transaksi->jadwal->lapangan->nama;
            }

            $prefix = ($transaksi->jenis === 'full') ? 'Pelunasan ' : 'DP ';
            $itemDetails = [
                [
                    'id' => (string) $transaksi->order_id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => mb_substr($prefix.$namaLapangan, 0, 50),
                ],
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $transaksi->order_id,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $firstName,
                'email' => $email,
                'phone' => $phone,
            ],
            'item_details' => $itemDetails,
        ];

        $snapToken = Snap::getSnapToken($params);

        // Simpan snap_token ke record transaksi di database untuk caching/reusable payment attempt
        $transaksi->snap_token = $snapToken;
        $transaksi->save();

        return $snapToken;
    }

    /**
     * Sinkronisasi status transaksi & booking langsung ke Midtrans API.
     */
    public function syncStatus(Transaksi $transaksi): ?Transaksi
    {
        if (empty($transaksi->order_id)) {
            return $transaksi;
        }

        $this->initMidtrans();

        try {
            $midStatus = \Midtrans\Transaction::status($transaksi->order_id);
            if (! $midStatus) {
                return $transaksi;
            }

            $transactionStatus = $midStatus->transaction_status ?? null;
            $fraudStatus = $midStatus->fraud_status ?? 'accept';
            $paymentType = $midStatus->payment_type ?? null;
            $transactionId = $midStatus->transaction_id ?? null;

            if ($paymentType) {
                $transaksi->payment_type = $paymentType;
            }
            if ($transactionId) {
                $transaksi->midtrans_transaction_id = $transactionId;
            }
            if ($transactionStatus) {
                $transaksi->transaction_status = $transactionStatus;
            }

            $jadwal = $transaksi->jadwal;

            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                $transaksi->status = 'berhasil';
                $transaksi->dibayar_pada = $transaksi->dibayar_pada ?? now();
                $transaksi->save();

                if ($jadwal) {
                    $jadwal->status_pembayaran = ($transaksi->jenis === 'full') ? 'lunas_online' : 'dp_terverifikasi';
                    $jadwal->status = 'terverifikasi';

                    if (empty($jadwal->kode_qr)) {
                        $jadwal->kode_qr = 'LPNG-QR-'.strtoupper(Str::random(12)).'-'.time();
                    }
                    if (empty($jadwal->token_manual)) {
                        $jadwal->token_manual = strtoupper(Str::random(6));
                    }
                    $jadwal->save();
                }
            } elseif ($transactionStatus === 'pending') {
                $transaksi->status = 'pending';
                $transaksi->save();
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $transaksi->status = 'gagal';
                $transaksi->save();

                if ($jadwal && $jadwal->status_pembayaran === 'belum_bayar') {
                    $jadwal->status = 'dibatalkan';
                    $jadwal->save();
                }
            } else {
                $transaksi->save();
            }

            return $transaksi;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("PaymentService syncStatus failed for {$transaksi->order_id}: " . $e->getMessage());
            return $transaksi;
        }
    }

    /**
     * Cari transaksi berdasarkan order_id dan sinkronkan dengan Midtrans.
     */
    public function syncByOrderId(string $orderId): ?Transaksi
    {
        $transaksi = Transaksi::where('order_id', $orderId)
            ->orWhere('referensi_midtrans', $orderId)
            ->first();

        if ($transaksi) {
            return $this->syncStatus($transaksi);
        }

        return null;
    }
}

