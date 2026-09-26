<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanAkademikSiswa extends Model
{
    protected $fillable = ['siswa_id', 'tahun_ajaran_id', 'guru_id', 'catatan'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}