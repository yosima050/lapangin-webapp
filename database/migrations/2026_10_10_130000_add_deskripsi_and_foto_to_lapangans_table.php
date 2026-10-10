<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lapangans', function (Blueprint $table) {
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lapangans', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'foto']);
        });
    }
};
