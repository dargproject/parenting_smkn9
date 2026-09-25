<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kategori',
        'kelompok',
        'beban_jp',
        'guru_id',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function tujuanPembelajarans()
    {
        return $this->hasMany(TujuanPembelajaran::class);
    }

    public function nilaiSas()
    {
        return $this->hasMany(NilaiSas::class);
    }

    public function nilaiPklUkks()
    {
        return $this->hasMany(NilaiPklUkk::class);
    }

    public function catatanKompetensis()
    {
        return $this->hasMany(CatatanKompetensi::class);
    }
}
