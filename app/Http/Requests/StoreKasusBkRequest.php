<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKasusBkRequest extends FormRequest
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
            'kategori_id' => 'nullable|exists:kategori_kasus,id',
            'judul' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'deskripsi' => 'required|string',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'tindak_lanjut' => 'nullable|string',
            'lampiran' => 'nullable|array|max:5',
            'lampiran.*' => 'file|max:12288|mimes:pdf,jpg,jpeg,png,docx',
            'lampiran_dihapus' => 'nullable|array',
            'lampiran_dihapus.*' => 'integer|exists:lampiran_kasus_bks,id',
        ];
    }
}
