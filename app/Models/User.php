<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'email', 'no_hp', 'password_hash', 'role'])]
#[Hidden(['password_hash'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

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
