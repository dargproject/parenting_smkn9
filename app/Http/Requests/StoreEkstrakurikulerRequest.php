<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEkstrakurikulerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_ekskul' => ['required', 'string', 'max:255'],
            'pembina_ids' => ['nullable', 'array'],
            'pembina_ids.*' => ['exists:gurus,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
