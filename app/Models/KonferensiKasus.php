<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonferensiKasus extends Model
{
    protected $table = 'konferensi_kasuses';

    protected $fillable = [
        'kasus_bk_id',
        'tanggal_konferensi',
        'tempat_pertemuan',
        'tampilkan_ke_ortu',
    ];

    protected $casts = [
        'tanggal_konferensi' => 'date',
        'tampilkan_ke_ortu' => 'boolean',
    ];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }

    public function pesertas()
    {
        return $this->hasMany(KonferensiKasusPeserta::class);
    }
}
