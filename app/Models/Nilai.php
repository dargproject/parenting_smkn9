<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $fillable = [
        'siswa_id',
        'mata_pelajaran_id',
        'nilai_tugas1',
        'nilai_tugas2',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'catatan_guru',
        'tahun_ajaran_id',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}
