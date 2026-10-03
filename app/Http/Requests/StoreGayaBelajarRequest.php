<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGayaBelajarRequest extends FormRequest
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
            'jawaban.Visual' => 'nullable|array',
            'jawaban.Visual.*' => 'integer',
            'jawaban.Auditorial' => 'nullable|array',
            'jawaban.Auditorial.*' => 'integer',
            'jawaban.Kinestetik' => 'nullable|array',
            'jawaban.Kinestetik.*' => 'integer',
            'hasil' => 'nullable|in:Visual,Auditorial,Kinestetik',
            'catatan' => 'nullable|string',
            'faktor_penghambat' => 'nullable|string',
            'faktor_pendukung' => 'nullable|string',
            'tampilkan_ke_ortu' => 'nullable|boolean',
        ];
    }
}
