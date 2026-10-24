<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jadwals')) {
            return;
        }

        if (! Schema::hasColumn('jadwals', 'checked_in_at')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->timestamp('checked_in_at')->nullable();
            });
        }

        if (! Schema::hasColumn('jadwals', 'kasir_id')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->uuid('kasir_id')->nullable();
            });
        }

        $this->createIndexIfMissing(
            'jadwals',
            'kode_qr',
            'jadwals_kode_qr_index'
        );

        $this->createIndexIfMissing(
            'jadwals',
            'token_manual',
            'jadwals_token_manual_index'
        );

        $this->createKasirForeignKeyIfMissing();
    }

    private function createIndexIfMissing(
        string $tableName,
        string $columnName,
        string $indexName
    ): void {
        if (
            ! Schema::hasTable($tableName) ||
            ! Schema::hasColumn($tableName, $columnName)
        ) {
            return;
        }

        $indexes = Schema::getIndexes($tableName);

        foreach ($indexes as $index) {
            if (
                ($index['name'] ?? null) === $indexName ||
                ($index['columns'] ?? []) === [$columnName]
            ) {
                return;
            }
        }

        Schema::table($tableName, function (Blueprint $table) use (
            $columnName,
            $indexName
        ) {
            $table->index($columnName, $indexName);
        });
    }

    private function createKasirForeignKeyIfMissing(): void
    {
        if (
            ! Schema::hasTable('pelanggans') ||
            ! Schema::hasColumn('pelanggans', 'id') ||
            ! Schema::hasColumn('jadwals', 'kasir_id')
        ) {
            return;
        }

        $foreignKeys = Schema::getForeignKeys('jadwals');

        foreach ($foreignKeys as $foreignKey) {
            if (
                in_array('kasir_id', $foreignKey['columns'] ?? [], true)
            ) {
                return;
            }
        }

        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreign('kasir_id', 'jadwals_kasir_id_foreign')
                ->references('id')
                ->on('pelanggans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Tidak menghapus kolom audit atau indeks yang mungkin sudah
        // digunakan oleh data dan aplikasi yang berjalan.
    }
};