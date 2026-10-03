<?php

namespace App\Http\Requests;

use App\Models\Sosiometri;
use Illuminate\Foundation\Http\FormRequest;

class StoreSosiometriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'pilihan' => 'nullable|array',
        ];

        foreach (array_keys(Sosiometri::PERTANYAAN) as $key) {
            $rules["pilihan.{$key}"] = 'nullable|array|max:3';
            $rules["pilihan.{$key}.*"] = 'nullable|exists:siswas,id';
        }

        return $rules;
    }
}
