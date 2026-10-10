<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reschedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jadwal_id');
            $table->date('tanggal_baru');
            $table->time('jam_mulai_baru');
            $table->time('jam_selesai_baru');
            $table->enum('status', [
                'diajukan',
                'disetujui',
                'ditolak',
            ]);
            $table->text('alasan_pengajuan');
            $table->timestamp('created_at');

            $table->foreign('jadwal_id')
                ->references('id')
                ->on('jadwals')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reschedules');
    }
};