<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tradehub_id',
        'name',
        'email',
        'password',
        'role',
        'vip_valid_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'vip_valid_until' => 'datetime', // Mengubah waktu menjadi objek Carbon
        ];
    }

    // Relasi: Satu User punya banyak riwayat pembayaran
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Relasi: Satu User punya banyak daftar pantauan AI
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    // Fungsi otomatis untuk mengecek apakah VIP masih aktif
    public function hasActiveVip(): bool
    {
        // Superadmin selalu punya akses (Founder's Edition)
        if ($this->role === 'superadmin') {
            return true;
        }

        // Cek apakah role VIP dan waktunya belum melewati hari ini
        return $this->role === 'vip' && $this->vip_valid_until && $this->vip_valid_until->isFuture();
    }
}