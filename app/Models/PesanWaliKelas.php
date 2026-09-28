<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesanWaliKelas extends Model
{
    protected $table = 'pesan_wali_kelas';

    protected $fillable = ['siswa_id', 'guru_id', 'pesan', 'dibaca_at'];

    protected $casts = ['dibaca_at' => 'datetime'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
