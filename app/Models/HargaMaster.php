<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaMaster extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'lapangan_id',
        'jenis_hari',
        'tanggal_khusus',
        'jam_mulai',
        'jam_selesai',
        'harga',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_khusus' => 'date',
            'harga' => 'decimal:2',
        ];
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}
