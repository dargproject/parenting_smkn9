<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkstrakurikulerSiswa extends Model
{
    protected $fillable = ['ekstrakurikuler_id', 'siswa_id', 'tahun_ajaran_id'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
