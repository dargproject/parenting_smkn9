<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LampiranKasusBk extends Model
{
    protected $table = 'lampiran_kasus_bks';

    protected $fillable = ['kasus_bk_id', 'nama_file', 'path_file', 'tipe_file', 'ukuran'];

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }
}
