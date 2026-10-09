
<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Tidak perlu menghapus unique index,
        // karena migration awal tidak membuatnya.
    }

    public function down(): void
    {
        // Tidak ada perubahan yang perlu dibatalkan.
    }
};