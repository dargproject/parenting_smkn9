<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BimbinganIndividu extends Model
{
    protected $fillable = ['kasus_bk_id', 'tanggal_layanan'];

    protected $casts = ['tanggal_layanan' => 'date'];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }
}
