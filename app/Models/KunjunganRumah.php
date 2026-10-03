<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganRumah extends Model
{
    protected $fillable = [
        'kasus_bk_id',
        'tanggal_kunjungan',
        'status',
        'tampilkan_ke_ortu',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'tampilkan_ke_ortu' => 'boolean',
    ];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }
}
