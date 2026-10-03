<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengunduranDiriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswas,id',
            'nama_ortu_wali' => 'required|string|max:255',
            'alamat_ortu_wali' => 'required|string',
            'alasan_pengunduran' => 'required|string',
            'tanggal_pengunduran' => 'required|date',
        ];
    }
}
