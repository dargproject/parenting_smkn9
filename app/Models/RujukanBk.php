<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RujukanBk extends Model
{
    protected $fillable = [
        'siswa_id',
        'dirujuk_oleh',
        'kategori',
        'alasan',
        'status',
        'kasus_bk_id',
        'ditangani_oleh',
        'catatan_penolakan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dirujukOleh()
    {
        return $this->belongsTo(Guru::class, 'dirujuk_oleh');
    }

    public function ditanganiOleh()
    {
        return $this->belongsTo(Guru::class, 'ditangani_oleh');
    }

    public function kasusBk()
    {
        return $this->belongsTo(KasusBk::class);
    }
}
