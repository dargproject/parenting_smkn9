<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TujuanPembelajaran extends Model
{
    protected $fillable = ['mata_pelajaran_id', 'tahun_ajaran_id', 'kode', 'deskripsi', 'urutan'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function nilaiLms()
    {
        return $this->hasMany(NilaiLm::class);
    }
}
