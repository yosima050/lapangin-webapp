<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Jadwal extends Model
{
    use HasUuids;

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function transaksi(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'jadwal_id');
    }
}