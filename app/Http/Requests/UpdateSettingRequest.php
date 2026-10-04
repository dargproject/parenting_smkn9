<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:50'],
            'alamat_sekolah' => ['nullable', 'string', 'max:2000'],
            'logo_sekolah' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'gambar_login' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'nama_kepsek' => ['nullable', 'string', 'max:255'],
            'nip_kepsek' => ['nullable', 'string', 'max:50'],
            'kktp_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'kktp_margin' => ['required', 'integer', 'min:0', 'max:50'],
            'alpa_mingguan_threshold' => ['required', 'integer', 'min:1', 'max:6'],
        ];
    }
}
