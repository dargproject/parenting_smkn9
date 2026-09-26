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
            'nama_kepsek' => ['nullable', 'string', 'max:255'],
            'nip_kepsek' => ['nullable', 'string', 'max:50'],
            'bobot_lm' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_sas' => ['required', 'numeric', 'min:0', 'max:100'],
            'kktp_threshold' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('bobot_lm') && $this->filled('bobot_sas') && (float) $this->bobot_lm + (float) $this->bobot_sas !== 100.0) {
                $validator->errors()->add('bobot_sas', 'Total bobot LM dan SAS harus 100%.');
            }
        });
    }
}
