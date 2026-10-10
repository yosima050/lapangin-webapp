<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->integer('sewa_rompi')->default(0)->change();
            $table->integer('sewa_bola')->default(0)->change();
            $table->decimal('denda_pembatalan', 12, 2)
                ->default(0)
                ->change();
            $table->timestamp('created_at')
                ->useCurrent()
                ->change();
        });
    }

    public function down(): void
    {
        
    }
};
