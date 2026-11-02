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
        Schema::create('lapangan_perlengkapan', function (Blueprint $table) {
            $table->uuid('lapangan_id');
            $table->uuid('perlengkapan_id');

            $table->foreign('lapangan_id')
                ->references('id')
                ->on('lapangans')
                ->cascadeOnDelete();

            $table->foreign('perlengkapan_id')
                ->references('id')
                ->on('perlengkapans')
                ->cascadeOnDelete();

            $table->primary(['lapangan_id', 'perlengkapan_id']);
            $table->index(['lapangan_id', 'perlengkapan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapangan_perlengkapan');
    }
};
