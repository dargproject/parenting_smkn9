<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    protected $fillable = [
        'siswa_id',
        'master_pelanggaran_id',
        'tanggal',
        'kategori',
        'judul',
        'deskripsi',
        'poin',
        'pelapor_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pelapor()
    {
        return $this->belongsTo(Guru::class, 'pelapor_id');
    }

    public function masterPelanggaran()
    {
        return $this->belongsTo(MasterPelanggaran::class);
    }
}
