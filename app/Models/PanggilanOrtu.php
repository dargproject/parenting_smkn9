<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanggilanOrtu extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'waktu',
        'ruang',
        'alasan',
        'status',
        'pemanggil_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pemanggil()
    {
        return $this->belongsTo(Guru::class, 'pemanggil_id');
    }
}
