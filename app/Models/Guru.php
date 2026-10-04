<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guru extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nama',
        'nip',
        'password',
        'email',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function kelasBk()
    {
        return $this->belongsToMany(Kelas::class, 'guru_bk_kelas');
    }

    public function ekstrakurikulerBinaan()
    {
        return $this->hasMany(Ekstrakurikuler::class, 'guru_id');
    }
}
