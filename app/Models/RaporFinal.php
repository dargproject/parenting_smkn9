<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaporFinal extends Model
{
    protected $fillable = ['siswa_id', 'tahun_ajaran_id', 'status', 'dirilis_at', 'dirilis_oleh'];

    protected $casts = [
        'dirilis_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function dirilisOleh()
    {
        return $this->belongsTo(Guru::class, 'dirilis_oleh');
    }
}
