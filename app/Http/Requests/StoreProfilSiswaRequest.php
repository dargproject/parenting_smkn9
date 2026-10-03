<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfilSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'agama' => 'nullable|string|max:50',
            'anak_ke' => 'nullable|integer|min:1',
            'jml_saudara' => 'nullable|integer|min:0',
            'asal_smp' => 'nullable|string|max:200',
            'hobi' => 'nullable|string|max:200',
            'bakat' => 'nullable|string|max:200',
            'rencana_lulus' => 'nullable|in:Bekerja,Kuliah,Menikah',
            'detail_rencana_lulus' => 'nullable|string|max:255',
        ];
    }
}
