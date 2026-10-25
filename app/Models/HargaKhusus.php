<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaKhusus extends Model
{
    use HasUuids;

    protected $table = 'harga_khusus';

    public $timestamps = false;

    protected $fillable = [
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'harga',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'harga' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}