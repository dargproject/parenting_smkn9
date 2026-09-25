<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriPengumuman extends Model
{
    protected $table = 'kategori_pengumumans';

    protected $fillable = ['nama', 'slug'];
}
