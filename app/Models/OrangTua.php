<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrangTua extends Authenticatable
{
    use Notifiable;

    protected $table = 'orang_tuas';

    protected $fillable = ['nama', 'username', 'email', 'password', 'is_active', 'harus_lengkapi_profil'];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'harus_lengkapi_profil' => 'boolean',
    ];

    public function links()
    {
        return $this->hasMany(OrangTuaSiswa::class);
    }

    /**
     * Semua siswa yang tertaut ke akun ini (anak kembar/kakak-adik bisa lebih dari satu),
     * lengkap dengan status persetujuan per anak lewat pivot.is_active.
     */
    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'orang_tua_siswa')->withPivot('is_active')->withTimestamps();
    }

    /**
     * Anak yang tautannya sudah disetujui -- yang ditampilkan di pemilih anak & dashboard.
     * Anak yang masih menunggu persetujuan tidak ikut di sini.
     */
    public function siswaAktif()
    {
        return $this->siswas()->wherePivot('is_active', true)->orderBy('nama');
    }
}
