<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasusBk extends Model
{
    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'judul',
        'kategori',
        'kategori_id',
        'deskripsi',
        'status',
        'prioritas',
        'tanggal_mulai',
        'tanggal_selesai',
        'tindak_lanjut',
        'konselor_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function konselor()
    {
        return $this->belongsTo(Guru::class, 'konselor_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kategoriKasus()
    {
        return $this->belongsTo(KategoriKasus::class, 'kategori_id');
    }

    public function lampiran()
    {
        return $this->hasMany(LampiranKasusBk::class, 'kasus_bk_id');
    }

    public function bimbinganIndividu()
    {
        return $this->hasOne(BimbinganIndividu::class);
    }

    public function bimbinganKelompok()
    {
        return $this->hasOne(BimbinganKelompok::class);
    }

    public function kunjunganRumah()
    {
        return $this->hasOne(KunjunganRumah::class);
    }

    public function alihTangans()
    {
        return $this->hasMany(AlihTanganKasus::class);
    }

    public function alihTanganTerakhir()
    {
        return $this->hasOne(AlihTanganKasus::class)->latestOfMany();
    }

    public function konferensiKasuses()
    {
        return $this->hasMany(KonferensiKasus::class);
    }

    public function konferensiTerakhir()
    {
        return $this->hasOne(KonferensiKasus::class)->latestOfMany('tanggal_konferensi');
    }
}
