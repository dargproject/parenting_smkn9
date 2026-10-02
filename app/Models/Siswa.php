<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Siswa extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nis',
        'nisn',
        'nipd',
        'nama',
        'kelas_id',
        'password',
        'jenis_kelamin',
        'tanggal_lahir',
        'no_hp_ortu',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function catatanAkademiks()
    {
        return $this->hasMany(CatatanAkademikSiswa::class);
    }

    public function asesmenBks()
    {
        return $this->hasMany(AsesmenBk::class);
    }

    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }

    public function nilaiLms()
    {
        return $this->hasMany(NilaiLm::class);
    }

    public function nilaiSas()
    {
        return $this->hasMany(NilaiSas::class);
    }

    public function nilaiPklUkks()
    {
        return $this->hasMany(NilaiPklUkk::class);
    }

    public function catatanKompetensis()
    {
        return $this->hasMany(CatatanKompetensi::class);
    }

    public function catatanWaliKelas()
    {
        return $this->hasMany(CatatanWaliKelas::class);
    }

    public function raporFinals()
    {
        return $this->hasMany(RaporFinal::class);
    }

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class);
    }

    public function profilSiswa()
    {
        return $this->hasOne(ProfilSiswa::class);
    }

    public function dataKeluarga()
    {
        return $this->hasOne(DataKeluargaSiswa::class);
    }

    public function kasusBks()
    {
        return $this->hasMany(KasusBk::class);
    }
}
