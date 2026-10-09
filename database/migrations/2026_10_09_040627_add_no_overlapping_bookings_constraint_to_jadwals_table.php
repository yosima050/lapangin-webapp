<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Constraint ini hanya didukung oleh PostgreSQL.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::transaction(function (): void {
            DB::statement(
                'CREATE EXTENSION IF NOT EXISTS btree_gist'
            );

            DB::statement(
                'LOCK TABLE jadwals IN ACCESS EXCLUSIVE MODE'
            );

            $jamTidakValid = DB::selectOne("
                SELECT EXISTS (
                    SELECT 1
                    FROM jadwals
                    WHERE jam_mulai >= jam_selesai
                ) AS ada_jam_tidak_valid
            ");

            if ($jamTidakValid->ada_jam_tidak_valid) {
                throw new RuntimeException(
                    'Ada jadwal dengan jam_mulai >= jam_selesai.'
                );
            }

            $bentrok = DB::selectOne("
                SELECT EXISTS (
                    SELECT 1
                    FROM jadwals a
                    JOIN jadwals b
                      ON a.lapangan_id = b.lapangan_id
                     AND a.id < b.id
                     AND a.tanggal = b.tanggal
                     AND (a.jam_mulai, a.jam_selesai)
                         OVERLAPS
                         (b.jam_mulai, b.jam_selesai)
                    WHERE a.status <> 'dibatalkan'
                      AND b.status <> 'dibatalkan'
                ) AS ada_bentrok
            ");

            if ($bentrok->ada_bentrok) {
                throw new RuntimeException(
                    'Terdapat jadwal aktif yang bertabrakan.'
                );
            }

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

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE jadwals
            DROP CONSTRAINT IF EXISTS
            jadwals_no_overlapping_bookings
        ");
    }
};