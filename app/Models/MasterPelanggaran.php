<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPelanggaran extends Model
{
    protected $fillable = ['nama_pelanggaran', 'pasal_id', 'jenis_id', 'is_active'];

    public function pasal()
    {
        return $this->belongsTo(Pasal::class);
    }

    public function jenisPelanggaran()
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_id');
    }

    public function getPoinAttribute()
    {
        return $this->jenisPelanggaran?->poin;
    }
}
