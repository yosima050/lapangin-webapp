<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reschedule extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'jadwal_id',
        'tanggal_baru',
        'jam_mulai_baru',
        'jam_selesai_baru',
        'status',
        'alasan_pengajuan',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($reschedule) {
            if (empty($reschedule->created_at)) {
                $reschedule->created_at = now();
            }
        });
    }

    
    protected function casts(): array
    {
        return [
            'tanggal_baru' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}