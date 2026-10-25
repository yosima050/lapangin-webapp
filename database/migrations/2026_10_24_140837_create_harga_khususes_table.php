<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('harga_khusus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lapangan_id');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            // Tarif final untuk slot waktu ini; harga master tidak diubah.
            $table->decimal('harga', 12, 2);

            $table->string('keterangan', 150)->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('lapangan_id')
                ->references('id')
                ->on('lapangans')
                ->cascadeOnDelete();

            $table->index(['lapangan_id', 'tanggal', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_khusus');
    }
};
