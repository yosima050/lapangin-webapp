<?php

namespace App\Models;

use App\Models\HargaKhusus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'is_active',
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

    public function getFotoThumbnailUrlAttribute(): string
    {
        $url = $this->foto_url;
        if (str_contains($url, 'images.unsplash.com')) {
            // Replace / inject compact parameters for thumbnails
            $baseUrl = explode('?', $url)[0];
            return $baseUrl . '?auto=format&fit=crop&w=150&h=150&fm=webp&q=75';
        }
        return $url;
    }

    public function getFotoCardUrlAttribute(): string
    {
        $url = $this->foto_url;
        if (str_contains($url, 'images.unsplash.com')) {
            $baseUrl = explode('?', $url)[0];
            return $baseUrl . '?auto=format&fit=crop&w=480&h=440&fm=webp&q=75';
        }
        return $url;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function hargaKhusus(): HasMany
    {
        return $this->hasMany(HargaKhusus::class, 'lapangan_id');
    }

    public function perlengkapans(): BelongsToMany
    {
        return $this->belongsToMany(
            Perlengkapan::class,
            'lapangan_perlengkapan',
            'lapangan_id',
            'perlengkapan_id'
        );
    }
}