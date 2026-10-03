<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBimbinganKelompokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|exists:siswas,id',
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
