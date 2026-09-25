<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'wali_kelas_id',
        'guru_wali_id',
    ];

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function guruWali()
    {
        return $this->belongsTo(Guru::class, 'guru_wali_id');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }
}
