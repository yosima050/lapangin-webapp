<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'lapangan_id',
        'pelanggan_id',
        'kasir_id',
        'checked_in_at',
        'nama_tamu',
        'no_wa_tamu',
        'email_tamu',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'harga_disepakati',
        'biaya_layanan',
        'status_pembayaran',
        'status',
        'kode_qr',
        'token_manual',
        'sewa_rompi',
        'sewa_bola',
        'denda_pembatalan',
        'catatan_kasir',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'harga_disepakati' => 'decimal:2',
            'denda_pembatalan' => 'decimal:2',
            'created_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function setJamSelesaiAttribute($value): void
    {
        if (in_array(substr((string) $value, 0, 5), ['00:00', '24:00'], true)) {
            $this->attributes['jam_selesai'] = '24:00:00';
        } else {
            $this->attributes['jam_selesai'] = $value;
        }
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'kasir_id');
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'jadwal_id');
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(Reschedule::class, 'jadwal_id');
    }

    public static function autoCancelExpiredPendingBookings(): int
    {
        $expiredJadwals = static::where('status', 'pending')
            ->where('status_pembayaran', 'belum_bayar')
            ->where('created_at', '<=', now()->subMinutes(10))
            ->get();

        $count = 0;

        foreach ($expiredJadwals as $jadwal) {
            $jadwal->update([
                'status' => 'dibatalkan',
            ]);

            Transaksi::where('jadwal_id', $jadwal->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'gagal',
                ]);

            $count++;
        }

        return $count;
    }
}