<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestasiNonAkademik extends Model
{
    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'nama_prestasi',
        'tingkat',
        'peringkat',
        'tanggal',
        'penyelenggara',
        'keterangan',
        'guru_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
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
