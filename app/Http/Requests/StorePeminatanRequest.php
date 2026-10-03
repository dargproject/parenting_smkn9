<?php

namespace App\Http\Requests;

use App\Models\Peminatan;
use Illuminate\Foundation\Http\FormRequest;

class StorePeminatanRequest extends FormRequest
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
            'jawaban' => 'nullable|array',
            'pilihan1' => 'nullable|string|max:255',
            'pilihan2' => 'nullable|string|max:255',
            'pilihan3' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ];

        foreach (Peminatan::SECTIONS as $section) {
            $rules["jawaban.{$section}"] = 'nullable|array';
            $rules["jawaban.{$section}.*"] = 'string';
        }

        return $rules;
    }
}
