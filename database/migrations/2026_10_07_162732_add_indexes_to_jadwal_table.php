<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->index(
                ['lapangan_id', 'tanggal'],
                'jadwals_lapangan_id_tanggal_index'
            );
        });

        Schema::table('harga_masters', function (Blueprint $table) {
            $table->index(
                ['lapangan_id', 'jenis_hari'],
                'harga_masters_lapangan_id_jenis_hari_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropIndex('jadwals_lapangan_id_tanggal_index');
        });

        Schema::table('harga_masters', function (Blueprint $table) {
            $table->dropIndex('harga_masters_lapangan_id_jenis_hari_index');
        });
    }
};