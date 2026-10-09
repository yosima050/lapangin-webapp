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
        Schema::create('harga_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lapangan_id');
            $table->enum('jenis_hari', ['weekday', 'weekend']);
            $table->date('tanggal_khusus')->nullable();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('harga', 12, 2);

            $table->foreign('lapangan_id')
                ->references('id')
                ->on('lapangans')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_masters');
    }
};