<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nama_desa',
        'kecamatan',
        'kabupaten',
        'nama_ketua_pkk',
        'telepon',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relations
    public function keluargas(): HasMany
    {
        return $this->hasMany(Keluarga::class);
    }

    public function agendaKegiatans(): HasMany
    {
        return $this->hasMany(AgendaKegiatan::class);
    }

    public function kunjunganRumahs(): HasMany
    {
        return $this->hasMany(KunjunganRumah::class);
    }


    // Accessors
    public function getDesaLengkapAttribute(): string
    {
        return "{$this->nama_desa}, {$this->kecamatan}, {$this->kabupaten}";
    }
}