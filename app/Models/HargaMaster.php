<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaMaster extends Model
{
    public $timestamps = false;
    
    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}