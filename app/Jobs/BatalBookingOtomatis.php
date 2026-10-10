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
            
            // Cek status transaksi yang terkait dengan jadwal ini
            $transaksi = Transaksi::where('jadwal_id', $this->jadwal_id)->first();

            // Jika belum lunas (masih pending)
            if (!$transaksi || $transaksi->status === 'pending') {
                
                // Kembalikan slot menjadi kosong/tersedia
                $jadwal->status = 'tersedia';
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
