<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lapangan extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'kategori',
        'jam_buka',
        'jam_tutup',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'lapangan_id');
    }

    public function hargaMasters(): HasMany
    {
        return $this->hasMany(HargaMaster::class, 'lapangan_id');
    }
}