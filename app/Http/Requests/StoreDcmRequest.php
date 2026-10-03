<?php

namespace App\Http\Requests;

use App\Models\Dcm;
use Illuminate\Foundation\Http\FormRequest;

class StoreDcmRequest extends FormRequest
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
            'kesimpulan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ];

        foreach (array_keys(Dcm::SECTIONS) as $letter) {
            $rules["jawaban.{$letter}"] = 'nullable|array';
            $rules["jawaban.{$letter}.*"] = 'string';
        }

        return $rules;
    }
}
