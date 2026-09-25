<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'semester',
        'is_active',
    ];

    public function jadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }
}
