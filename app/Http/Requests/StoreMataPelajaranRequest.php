<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMataPelajaranRequest extends FormRequest
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
        return ['kode_mapel' => ['nullable', 'string', 'max:50', Rule::unique('mata_pelajarans', 'kode_mapel')->ignore($this->route('mataPelajaran'))], 'nama_mapel' => ['required', 'string', 'max:255', Rule::unique('mata_pelajarans', 'nama_mapel')->ignore($this->route('mataPelajaran'))], 'kelompok' => 'nullable|in:A,B,C', 'kategori' => 'required|in:Nasional,Kejuruan', 'beban_jp' => 'required|integer|min:1|max:20', 'wajib_dinilai' => 'nullable|boolean'];
    }
}
