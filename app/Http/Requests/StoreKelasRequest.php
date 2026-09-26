<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKelasRequest extends FormRequest
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
        return ['nama_kelas' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('kelas', 'nama_kelas')->ignore($this->route('kelas'))], 'tingkat' => 'required|in:X,XI,XII', 'jurusan' => 'required|string|max:100', 'wali_kelas_id' => 'nullable|exists:gurus,id'];
    }
}
