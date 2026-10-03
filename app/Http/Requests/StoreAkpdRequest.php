<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAkpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'jawaban' => 'nullable|array',
            'jawaban.*' => 'nullable|in:Ya,Tidak',
        ];
    }
}
