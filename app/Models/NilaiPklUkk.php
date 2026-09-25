<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiPklUkk extends Model
{
    protected $fillable = ['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'jenis', 'nilai', 'catatan', 'guru_id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function scopePkl($query)
    {
        return $query->where('jenis', 'pkl');
    }

    public function scopeUkk($query)
    {
        return $query->where('jenis', 'ukk');
    }
}
