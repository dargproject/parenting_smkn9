<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKunjunganRumahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal_kunjungan' => 'required|date',
            'status' => 'required|in:diproses,ditunda,dibatalkan',
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
