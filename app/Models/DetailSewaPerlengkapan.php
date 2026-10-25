<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailSewaPerlengkapan extends Model
{
    use HasUuids;

    protected $table = 'detail_sewa_perlengkapans';

    public $timestamps = false;

    protected $fillable = [
        'jadwal_id',
        'nama_perlengkapan',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}
