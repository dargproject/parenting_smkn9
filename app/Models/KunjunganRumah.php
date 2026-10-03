<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganRumah extends Model
{
    protected $fillable = [
        'kasus_bk_id',
        'tanggal_kunjungan',
        'status',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }
}
