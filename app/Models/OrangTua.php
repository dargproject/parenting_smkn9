<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrangTua extends Authenticatable
{
    use Notifiable;

    protected $table = 'orang_tuas';

    protected $fillable = ['siswa_id', 'nama', 'username', 'email', 'password', 'is_active'];

    protected $hidden = [
        'password',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
