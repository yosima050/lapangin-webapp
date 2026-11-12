<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memindahkan data tanggal_khusus dari tabel harga_masters ke tabel harga_khusus
     * sehingga semua harga khusus terpusat di halaman Pengaturan Harga Khusus.
     */
    public function up(): void
    {
        if (Schema::hasTable('harga_masters') && Schema::hasTable('harga_khusus')) {
            $masters = DB::table('harga_masters')
                ->whereNotNull('tanggal_khusus')
                ->get();

            foreach ($masters as $m) {
                // Pastikan lapangan masih ada di database
                $lapanganExists = DB::table('lapangans')->where('id', $m->lapangan_id)->exists();
                if (! $lapanganExists) {
                    continue;
                }

                $exists = DB::table('harga_khusus')
                    ->where('lapangan_id', $m->lapangan_id)
                    ->where('tanggal', $m->tanggal_khusus)
                    ->where('jam_mulai', $m->jam_mulai ?: '07:00:00')
                    ->where('jam_selesai', $m->jam_selesai ?: '24:00:00')
                    ->exists();

                if (! $exists) {
                    DB::table('harga_khusus')->insert([
                        'id' => (string) Str::uuid(),
                        'lapangan_id' => $m->lapangan_id,
                        'tanggal' => $m->tanggal_khusus,
                        'jam_mulai' => $m->jam_mulai ?: '07:00:00',
                        'jam_selesai' => $m->jam_selesai ?: '24:00:00',
                        'harga' => $m->harga,
                        'keterangan' => 'Tarif Khusus Hari Libur / Tanggal Merah',
                        'is_active' => true,
                    ]);
                }
            }

            // Hapus data tanggal_khusus dari harga_masters agar harga master hanya untuk weekday & weekend dasar
            DB::table('harga_masters')
                ->whereNotNull('tanggal_khusus')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu di-revert karena data tetap aman di tabel harga_khusus
    }
};
