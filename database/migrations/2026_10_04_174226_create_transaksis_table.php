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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jadwal_id');

            $table->enum('jenis', [
                'dp',
                'pelunasan',
                'full',
                'refund'
            ]);

            $table->decimal('jumlah', 12, 2);

            $table->enum('metode', [
                'online',
                'tunai'
            ]);

            $table->enum('status', [
                'pending',
                'berhasil',
                'gagal'
            ]);

            $table->string('referensi_midtrans')->nullable();
            $table->timestamp('dibayar_pada');

            $table->foreign('jadwal_id')
                ->references('id')
                ->on('jadwals')
                ->cascadeOnDelete();

            $table->unique('jadwal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};