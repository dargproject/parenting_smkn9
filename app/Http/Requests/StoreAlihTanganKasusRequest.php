<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlihTanganKasusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kasus_bk_id' => 'required|exists:kasus_bks,id',
            'konselor_tujuan_id' => [
                'required',
                'integer',
                Rule::exists('gurus', 'id'),
                Rule::notIn([$this->user()->id]),
            ],
            'tanggal_alih' => 'required|date',
            'alasan_alih' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'konselor_tujuan_id.not_in' => 'Guru BK penerima tidak boleh sama dengan Anda sendiri.',
        ];
    }
}
