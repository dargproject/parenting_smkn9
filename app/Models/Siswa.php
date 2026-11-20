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

    public function penguranganPoins()
    {
        return $this->hasMany(PenguranganPoin::class);
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

    /**
     * Tautan pivot ke akun orang tua siswa ini (siswa_id unik di orang_tua_siswa, jadi paling
     * banyak satu baris) -- dipakai saat perlu tahu status persetujuan (pivot.is_active) khusus
     * siswa ini, beda dari status aktif akunnya sendiri yang bisa saja sudah aktif lebih dulu
     * lewat anak lain (kakak/adik) di akun yang sama.
     */
    public function linkOrtu()
    {
        return $this->hasOne(OrangTuaSiswa::class, 'siswa_id');
    }

    /**
     * Akun orang tua siswa ini, lewat tautan pivot -- tetap dikembalikan walau tautannya masih
     * menunggu persetujuan (sama seperti perilaku lama), supaya pemanggil yang sudah ada (cek
     * "sudah ada akun atau belum", ambil nama wali, dst.) tidak perlu berubah.
     */
    public function orangTua()
    {
        return $this->hasOneThrough(OrangTua::class, OrangTuaSiswa::class, 'siswa_id', 'id', 'id', 'orang_tua_id');
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

    public function prestasiNonAkademiks()
    {
        return $this->hasMany(PrestasiNonAkademik::class);
    }

    public function nilaiEkstrakurikulers()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }

    public function ekstrakurikulerSiswas()
    {
        return $this->hasMany(EkstrakurikulerSiswa::class);
    }
}
