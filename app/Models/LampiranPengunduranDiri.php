<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LampiranPengunduranDiri extends Model
{
    protected $fillable = ['pengunduran_diri_id', 'nama_file', 'path_file', 'tipe_file', 'ukuran'];

    public function pengunduranDiri()
    {
        return $this->belongsTo(PengunduranDiri::class);
    }
}
