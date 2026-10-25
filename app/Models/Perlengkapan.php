<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Perlengkapan extends Model
{
    use HasUuids;

    protected $table = 'perlengkapans';

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'gambar',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
