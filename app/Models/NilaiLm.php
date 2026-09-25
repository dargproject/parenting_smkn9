<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiLm extends Model
{
    protected $fillable = ['siswa_id', 'tujuan_pembelajaran_id', 'tahun_ajaran_id', 'nilai', 'guru_id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tujuanPembelajaran()
    {
        return $this->belongsTo(TujuanPembelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
