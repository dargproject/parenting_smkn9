<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'jadwal_pelajaran_id',
        'tanggal',
        'status',
        'keterangan',
        'is_verified',
        'verification_status',
        'verification_notes',
        'verifier',
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

    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class);
    }
}
