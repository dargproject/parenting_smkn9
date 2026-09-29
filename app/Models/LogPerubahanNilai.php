<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogPerubahanNilai extends Model
{
    const UPDATED_AT = null;

    protected $table = 'log_perubahan_nilai';

    protected $fillable = ['nilai_lm_id', 'kolom', 'nilai_lama', 'nilai_baru', 'guru_id'];

    public function nilaiLm()
    {
        return $this->belongsTo(NilaiLm::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
