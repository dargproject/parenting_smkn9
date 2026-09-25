<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasusBk extends Model
{
    protected $fillable = [
        'siswa_id',
        'judul',
        'kategori',
        'deskripsi',
        'status',
        'konselor_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function konselor()
    {
        return $this->belongsTo(Guru::class, 'konselor_id');
    }
}
