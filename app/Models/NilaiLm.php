<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiLm extends Model
{
    protected $fillable = ['siswa_id', 'tujuan_pembelajaran_id', 'tahun_ajaran_id', 'nilai', 'nilai_remedial', 'sudah_pengayaan', 'catatan_pengayaan', 'guru_id'];

    protected $casts = ['sudah_pengayaan' => 'boolean'];

    /**
     * Nilai yang dipakai untuk semua perhitungan (NA, status, dsb): nilai remedial
     * menggantikan nilai asli bila diisi, tanpa menghapus nilai asli sebagai riwayat.
     */
    public function getNilaiEfektifAttribute(): ?int
    {
        return $this->nilai_remedial ?? $this->nilai;
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tujuanPembelajaran()
    {
        return $this->belongsTo(TujuanPembelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function logs()
    {
        return $this->hasMany(LogPerubahanNilai::class)->latest();
    }
}
