<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonferensiKasusPeserta extends Model
{
    protected $table = 'konferensi_kasus_pesertas';

    protected $fillable = [
        'konferensi_kasus_id',
        'nama_peserta',
        'peran_peserta',
    ];

    public function konferensiKasus()
    {
        return $this->belongsTo(KonferensiKasus::class);
    }
}
