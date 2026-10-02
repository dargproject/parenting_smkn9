<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataKeluargaSiswa extends Model
{
    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'nama_ayah',
        'nama_ibu',
        'pendidikan_ayah',
        'pendidikan_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'telp_ortu',
        'alamat_ayah',
        'alamat_ibu',
        'nomor_wa_ayah',
        'nomor_wa_ibu',
        'status_rumah',
        'lokasi_rumah',
        'dinding_rumah',
        'lantai_rumah',
        'jml_kamar',
        'punya_kamar_sendiri',
        'jml_tv',
        'kendaraan_mobil',
        'kendaraan_motor',
        'biaya_sekolah_dari',
        'kendaraan_ke_sekolah',
        'media_sosial',
    ];

    protected $casts = [
        'punya_kamar_sendiri' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
