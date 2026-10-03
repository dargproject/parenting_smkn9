<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BimbinganKelompokSiswa extends Model
{
    protected $fillable = [
        'bimbingan_kelompok_id',
        'siswa_id',
    ];

    public function bimbinganKelompok()
    {
        return $this->belongsTo(BimbinganKelompok::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
