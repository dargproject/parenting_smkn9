<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasal extends Model
{
    protected $fillable = ['kode', 'nama'];

    public function masterPelanggarans()
    {
        return $this->hasMany(MasterPelanggaran::class);
    }
}
