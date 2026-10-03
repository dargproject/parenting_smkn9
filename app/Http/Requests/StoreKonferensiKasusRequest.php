<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKonferensiKasusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kasus_bk_id' => 'required|exists:kasus_bks,id',
            'tanggal_konferensi' => 'required|date',
            'tempat_pertemuan' => 'nullable|string|max:255',
            'peserta' => 'required|array|min:1',
            'peserta.*.nama_peserta' => 'required|string|max:255',
            'peserta.*.peran_peserta' => 'required|string|max:100',
            'lampiran' => 'nullable|array|max:5',
            'lampiran.*' => 'file|max:12288|mimes:pdf,jpg,jpeg,png,doc,docx',
            'lampiran_dihapus' => 'nullable|array',
            'lampiran_dihapus.*' => 'integer|exists:lampiran_kasus_bks,id',
        ];
    }
}
