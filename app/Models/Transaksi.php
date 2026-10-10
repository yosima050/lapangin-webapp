<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'jadwal_id',
        'jenis',
        'jumlah',
        'metode',
        'status',
        'referensi_midtrans',
        'midtrans_transaction_id',
        'snap_token',
        'dibayar_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'dibayar_pada' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}