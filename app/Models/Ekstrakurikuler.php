<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    protected $fillable = ['nama_ekskul', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pembinas()
    {
        return $this->belongsToMany(Guru::class, 'ekstrakurikuler_guru');
    }

    public function peserta()
    {
        return $this->hasMany(EkstrakurikulerSiswa::class);
    }

    public function nilai()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }
}
