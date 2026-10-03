<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDataKeluargaSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_ayah' => 'nullable|string|max:150',
            'nama_ibu' => 'nullable|string|max:150',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'telp_ortu' => 'nullable|string|max:20',
            'alamat_ayah' => 'nullable|string',
            'alamat_ibu' => 'nullable|string',
            'nomor_wa_ayah' => 'nullable|string|max:20',
            'nomor_wa_ibu' => 'nullable|string|max:20',
            'status_rumah' => 'nullable|string|max:50',
            'lokasi_rumah' => 'nullable|string|max:100',
            'dinding_rumah' => 'nullable|string|max:50',
            'lantai_rumah' => 'nullable|string|max:50',
            'jml_kamar' => 'nullable|integer|min:0',
            'punya_kamar_sendiri' => 'nullable|boolean',
            'jml_tv' => 'nullable|integer|min:0',
            'kendaraan_mobil' => 'nullable|integer|min:0',
            'kendaraan_motor' => 'nullable|integer|min:0',
            'biaya_sekolah_dari' => 'nullable|string|max:100',
            'kendaraan_ke_sekolah' => 'nullable|string|max:100',
            'media_sosial' => 'nullable|string|max:200',
        ];
    }
}
