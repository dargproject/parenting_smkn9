<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'deskripsi',
        'kategori',
        'pembuat_id',
    ];

    public function pembuat()
    {
        return $this->belongsTo(Guru::class, 'pembuat_id');
    }
}
