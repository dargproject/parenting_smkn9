<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasMataPelajaran extends Model
{
    protected $table = 'kelas_mata_pelajaran';

    protected $fillable = ['kelas_id', 'mata_pelajaran_id', 'guru_id'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
