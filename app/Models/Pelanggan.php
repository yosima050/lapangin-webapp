<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;

#[Fillable(['nama', 'name', 'email', 'no_hp', 'password_hash', 'role'])]
#[Hidden(['password_hash'])]
class Pelanggan extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, Notifiable, HasUuids, MustVerifyEmail;

    public $timestamps = false;

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'pelanggan_id');
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nama'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }

    protected function casts(): array
    {
        return [];
    }
}