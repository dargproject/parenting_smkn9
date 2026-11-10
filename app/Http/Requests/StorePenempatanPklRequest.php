<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenempatanPklRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'nama_iduka' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'nama_cp_industri' => ['nullable', 'string', 'max:255'],
            'no_cp_industri' => ['nullable', 'string', 'max:50'],
            'guru_pembimbing_id' => ['nullable', 'exists:gurus,id'],
        ];
    }
}
