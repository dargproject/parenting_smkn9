<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiEkstrakurikuler extends Model
{
    protected $fillable = ['siswa_id', 'ekstrakurikuler_id', 'tahun_ajaran_id', 'nilai', 'catatan', 'guru_id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
