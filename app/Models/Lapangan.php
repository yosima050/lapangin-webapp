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
        'deskripsi',
        'foto',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($lapangan) {
            if (empty($lapangan->created_at)) {
                $lapangan->created_at = now();
            }
        });
    }

    public function getFotoUrlAttribute(): string
    {
        if (!empty($this->foto)) {
            if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
                return $this->foto;
            }
            return asset('storage/' . $this->foto);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama) . '&background=E0E7FF&color=3730A3&size=512';
    }

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