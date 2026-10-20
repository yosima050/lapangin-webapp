<?php

namespace App\Jobs;

use App\Models\Jadwal;
use App\Models\Transaksi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BatalBookingOtomatis implements ShouldQueue
{
    use Queueable;

    protected $jadwal_id;

    /**
     * Create a new job instance.
     */
    public function __construct($jadwal_id)
    {
        $this->jadwal_id = $jadwal_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Cari jadwal yang telah dibooking
        $jadwal = Jadwal::find($this->jadwal_id);

        // Pastikan jadwal ditemukan dan statusnya masih pending
        if ($jadwal && $jadwal->status === 'pending') {
            // Mencegah auto-cancel prematur saat menggunakan queue driver sync (seperti di testing atau local dev)
            if ($jadwal->created_at && \Carbon\Carbon::parse($jadwal->created_at)->gt(now()->subMinutes(9)->subSeconds(50))) {
                return;
            }

            // Cek status transaksi yang terkait dengan jadwal ini
            $transaksi = Transaksi::where('jadwal_id', $this->jadwal_id)->first();

            // Jika belum lunas (masih pending)
            if (!$transaksi || in_array($transaksi->status, ['pending', 'gagal'], true)) {
                
                // Kembalikan slot menjadi dibatalkan agar exclusion constraint PostgreSQL membuka kembali slot
                $jadwal->status = 'dibatalkan';
                $jadwal->status_pembayaran = 'belum_bayar';
                $jadwal->save();

                // Update status transaksi menjadi gagal
                if ($transaksi) {
                    $transaksi->status = 'gagal';
                    $transaksi->save();
                }

                Log::info("Booking untuk jadwal ID {$this->jadwal_id} otomatis dibatalkan karena batas waktu pembayaran (10 menit) telah kedaluwarsa.");
            }
        }
    }
}
