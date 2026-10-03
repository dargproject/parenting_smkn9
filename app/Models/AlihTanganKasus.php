<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlihTanganKasus extends Model
{
    protected $table = 'alih_tangan_kasuses';

    protected $fillable = [
        'kasus_bk_id',
        'konselor_asal_id',
        'konselor_tujuan_id',
        'tanggal_alih',
        'alasan_alih',
        'tindak_lanjut',
    ];

    protected $casts = [
        'tanggal_alih' => 'date',
    ];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }

    public function konselorAsal()
    {
        return $this->belongsTo(Guru::class, 'konselor_asal_id');
    }

    public function konselorTujuan()
    {
        return $this->belongsTo(Guru::class, 'konselor_tujuan_id');
    }
}
