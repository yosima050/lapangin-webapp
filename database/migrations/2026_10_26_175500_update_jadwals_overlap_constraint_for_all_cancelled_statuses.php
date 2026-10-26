<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::transaction(function (): void {
            DB::statement('LOCK TABLE jadwals IN ACCESS EXCLUSIVE MODE');

            // 1. Perbarui jadwals_status_check agar mencakup 'cancelled' dan 'rejected'
            DB::statement('
                ALTER TABLE jadwals
                DROP CONSTRAINT IF EXISTS jadwals_status_check
            ');

            DB::statement("
                ALTER TABLE jadwals
                ADD CONSTRAINT jadwals_status_check
                CHECK (status IN ('pending', 'terverifikasi', 'lunas', 'selesai', 'dibatalkan', 'cancelled', 'rejected'))
            ");

            // 2. Perbarui jadwals_no_overlapping_bookings exclude constraint
            DB::statement('
                ALTER TABLE jadwals
                DROP CONSTRAINT IF EXISTS jadwals_no_overlapping_bookings
            ');

            DB::statement("
                ALTER TABLE jadwals
                ADD CONSTRAINT jadwals_no_overlapping_bookings
                EXCLUDE USING gist (
                    lapangan_id WITH =,
                    (
                        tsrange(
                            tanggal + jam_mulai,
                            tanggal + jam_selesai,
                            '[)'
                        )
                    ) WITH &&
                )
                WHERE (status NOT IN ('dibatalkan', 'cancelled', 'rejected'))
            ");
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::transaction(function (): void {
            DB::statement('LOCK TABLE jadwals IN ACCESS EXCLUSIVE MODE');

            // Normalisasi data: ubah status 'cancelled' dan 'rejected' menjadi 'dibatalkan'
            // agar memenuhi constraint status dan exclusion yang direstorasi.
            // CATATAN: Normalisasi ini menyebabkan hilangnya perbedaan status awal antara 'cancelled'/'rejected' dan 'dibatalkan'.
            DB::statement("
                UPDATE jadwals
                SET status = 'dibatalkan'
                WHERE status IN ('cancelled', 'rejected')
            ");

            DB::statement('
                ALTER TABLE jadwals
                DROP CONSTRAINT IF EXISTS jadwals_status_check
            ');

            DB::statement("
                ALTER TABLE jadwals
                ADD CONSTRAINT jadwals_status_check
                CHECK (status IN ('pending', 'terverifikasi', 'lunas', 'selesai', 'dibatalkan'))
            ");

            DB::statement('
                ALTER TABLE jadwals
                DROP CONSTRAINT IF EXISTS jadwals_no_overlapping_bookings
            ');

            DB::statement("
                ALTER TABLE jadwals
                ADD CONSTRAINT jadwals_no_overlapping_bookings
                EXCLUDE USING gist (
                    lapangan_id WITH =,
                    (
                        tsrange(
                            tanggal + jam_mulai,
                            tanggal + jam_selesai,
                            '[)'
                        )
                    ) WITH &&
                )
                WHERE (status <> 'dibatalkan')
            ");
        });
    }
};
