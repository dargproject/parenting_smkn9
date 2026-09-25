<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPelanggaran extends Model
{
    protected $fillable = ['kode', 'nama', 'poin'];

    public function masterPelanggarans()
    {
        return $this->hasMany(MasterPelanggaran::class, 'jenis_id');
    }
}
