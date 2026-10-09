
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Jalankan perubahan ini hanya jika menggunakan PostgreSQL.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE reschedules
            DROP CONSTRAINT IF EXISTS reschedules_status_check
        ");

        DB::statement("
            ALTER TABLE reschedules
            ADD CONSTRAINT reschedules_status_check
            CHECK (status IN ('diajukan', 'disetujui', 'ditolak'))
        ");

        DB::statement("
            ALTER TABLE reschedules
            ALTER COLUMN status SET DEFAULT 'diajukan'
        ");
    }

    public function down(): void
    {
        // Jalankan perubahan ini hanya jika menggunakan PostgreSQL.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE reschedules
            DROP CONSTRAINT IF EXISTS reschedules_status_check
        ");

        DB::statement("
            ALTER TABLE reschedules
            ADD CONSTRAINT reschedules_status_check
            CHECK (status IN ('diajukan', 'disetujui', 'ditolak'))
        ");

        DB::statement("
            ALTER TABLE reschedules
            ALTER COLUMN status SET DEFAULT 'diajukan'
        ");
    }
};