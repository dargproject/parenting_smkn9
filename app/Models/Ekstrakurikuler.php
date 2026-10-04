<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    protected $fillable = ['nama_ekskul', 'guru_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function peserta()
    {
        return $this->hasMany(EkstrakurikulerSiswa::class);
    }

    public function nilai()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }
}
