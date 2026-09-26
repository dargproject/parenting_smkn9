<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenBk extends Model
{
    protected $fillable = ['siswa_id', 'tahun_ajaran_id', 'konselor_id', 'tingkat_stres', 'minat_karir', 'catatan'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function konselor()
    {
        return $this->belongsTo(Guru::class, 'konselor_id');
    }
}