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
        Schema::create('jadwals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lapangan_id');
            $table->uuid('pelanggan_id')->nullable();
            $table->string('nama_tamu')->nullable();
            $table->string('no_wa_tamu')->nullable();
            $table->string('email_tamu')->nullable();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('harga_disepakati', 12, 2);
            $table->integer('biaya_layanan');
            $table->enum('status_pembayaran', [
                'belum_bayar',
                'dp_terverifikasi',
                'lunas_online',
                'lunas_kasir'
            ]);
            $table->string('status');
            $table->string('kode_qr')->nullable();
            $table->string('token_manual')->nullable();
            $table->integer('sewa_rompi');
            $table->integer('sewa_bola');
            $table->decimal('denda_pembatalan', 12, 2);
            $table->text('catatan_kasir')->nullable();
            $table->timestamp('created_at');
            $table->foreign('lapangan_id')
                ->references('id')
                ->on('lapangans')
                ->cascadeOnDelete();
            $table->foreign('pelanggan_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};