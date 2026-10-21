<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenguranganPoin extends Model
{
    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'tanggal',
        'kegiatan',
        'keterangan',
        'poin',
        'diberikan_oleh',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function diberikanOleh()
    {
        return $this->belongsTo(Guru::class, 'diberikan_oleh');
    }
}
