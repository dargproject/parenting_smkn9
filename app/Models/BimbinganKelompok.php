<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BimbinganKelompok extends Model
{
    protected $fillable = [
        'kasus_bk_id',
        'tanggal_layanan',
    ];

    protected $casts = [
        'tanggal_layanan' => 'date',
    ];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }

    public function pesertas()
    {
        return $this->hasMany(BimbinganKelompokSiswa::class);
    }

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'bimbingan_kelompok_siswas');
    }
}
