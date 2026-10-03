<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBimbinganIndividuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal_layanan' => 'required|date',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tindak_lanjut' => 'nullable|string',
            'lampiran' => 'nullable|array|max:5',
            'lampiran.*' => 'file|max:12288|mimes:pdf,jpg,jpeg,png,doc,docx',
            'lampiran_dihapus' => 'nullable|array',
            'lampiran_dihapus.*' => 'integer|exists:lampiran_kasus_bks,id',
        ];
    }
}
