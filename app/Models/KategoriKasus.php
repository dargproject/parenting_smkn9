<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKasus extends Model
{
    protected $table = 'kategori_kasus';

    protected $fillable = ['nama_kategori'];

    public function kasusBks()
    {
        return $this->hasMany(KasusBk::class, 'kategori_id');
    }
}
