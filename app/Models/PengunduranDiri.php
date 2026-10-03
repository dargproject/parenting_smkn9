<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengunduranDiri extends Model
{
    protected $fillable = [
        'siswa_id',
        'nama_ortu_wali',
        'alamat_ortu_wali',
        'alasan_pengunduran',
        'tanggal_pengunduran',
    ];

    protected $casts = [
        'tanggal_pengunduran' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function lampirans()
    {
        return $this->hasMany(LampiranPengunduranDiri::class);
    }
}
