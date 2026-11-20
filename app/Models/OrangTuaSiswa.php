<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tautan satu akun orang tua ke satu siswa -- satu akun bisa punya banyak tautan (anak kembar/
 * kakak-adik di sekolah ini), tapi satu siswa cuma boleh tertaut ke satu akun (siswa_id unik).
 * is_active di sini artinya "tautan khusus anak ini sudah disetujui wali kelas/admin", beda dari
 * is_active di OrangTua yang artinya "akunnya sendiri sudah pernah disetujui, boleh login".
 */
class OrangTuaSiswa extends Model
{
    protected $table = 'orang_tua_siswa';

    protected $fillable = ['orang_tua_id', 'siswa_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
