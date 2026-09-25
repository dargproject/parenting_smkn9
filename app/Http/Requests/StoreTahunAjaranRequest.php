<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTahunAjaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tahunAjaranId = $this->route('tahun_ajaran')?->id;

        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('tahun_ajarans', 'kode')->ignore($tahunAjaranId)],
            'nama' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'in:ganjil,genap'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
