<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SosiometriRespon extends Model
{
    protected $table = 'sosiometri_respons';

    protected $fillable = [
        'sosiometri_id',
        'siswa_dipilih_id',
        'nama_dipilih',
        'urutan',
        'pertanyaan',
    ];

    public function sosiometri()
    {
        return $this->belongsTo(Sosiometri::class);
    }

    public function siswaDipilih()
    {
        return $this->belongsTo(Siswa::class, 'siswa_dipilih_id');
    }
}
