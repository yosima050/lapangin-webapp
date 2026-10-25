
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_sewa_perlengkapans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('jadwal_id');
            $table->string('nama_perlengkapan', 100);
            $table->unsignedInteger('jumlah')->default(1);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('jadwal_id')
                ->references('id')
                ->on('jadwals')
                ->cascadeOnDelete();

            $table->index('jadwal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_sewa_perlengkapans');
    }
};
