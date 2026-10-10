<?php

namespace App\Http\Controllers;

use App\Jobs\BatalBookingOtomatis;
use App\Models\Jadwal;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Proses checkout booking lapangan
     */
    public function checkout(Request $request)
    {
        // Validasi input
        $request->validate([
            'jadwal_id' => 'required|uuid'
        ]);

        $jadwal_id = $request->jadwal_id;

        try {
            // Gunakan DB Transaction untuk memastikan atomicity
            $hasil = DB::transaction(function () use ($jadwal_id) {
                
                // 1. Ambil data jadwal & kunci baris menggunakan lockForUpdate() (Pencegahan Double-Booking)
                $jadwal = Jadwal::where('id', $jadwal_id)
                                ->lockForUpdate()
                                ->first();

                // 2. Cek ketersediaan jadwal
                if (!$jadwal) {
                    throw new \Exception('Jadwal tidak ditemukan.');
                }

                if ($jadwal->status !== 'tersedia') {
                    throw new \Exception('Maaf, jadwal ini sudah dibooking secara bersamaan oleh pengguna lain.');
                }

                // 3. Ubah status slot menjadi pending
                $jadwal->status = 'pending';
                $jadwal->save();

                // 4. (Opsional) Buat draft Transaksi
                $transaksi = new Transaksi();
                $transaksi->id = Str::uuid();
                $transaksi->jadwal_id = $jadwal->id;
                $transaksi->jenis = 'full';
                $transaksi->jumlah = $jadwal->harga_disepakati ?? 0;
                $transaksi->metode = 'online';
                $transaksi->status = 'pending';
                // Jika migration dibayar_pada tidak nullable, berikan default (akan diperbarui nanti saat lunas)
                $transaksi->dibayar_pada = now(); 
                $transaksi->save();

                return [
                    'jadwal' => $jadwal,
                    'transaksi' => $transaksi
                ];
            });

            // 5. Jadwalkan job untuk mengubah status kembali ke 'tersedia' setelah 10 menit (Timeout)
            BatalBookingOtomatis::dispatch($jadwal_id)->delay(now()->addMinutes(10));

            return response()->json([
                'status' => 'success',
                'pesan' => 'Jadwal berhasil dikunci. Silakan selesaikan pembayaran dalam 10 menit.',
                'data' => $hasil
            ], 200);

        } catch (\Exception $e) {
            // Rollback otomatis terjadi jika throw Exception di dalam DB::transaction
            return response()->json([
                'status' => 'error',
                'pesan' => $e->getMessage()
            ], 400);
        }
    }
}
