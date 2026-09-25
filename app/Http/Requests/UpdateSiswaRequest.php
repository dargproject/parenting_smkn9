<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiswaRequest extends FormRequest
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
        return ['nis'=>'required|string|max:50|unique:siswas,nis,'.$this->route('siswa')->id,'nisn'=>'nullable|string|max:50|unique:siswas,nisn,'.$this->route('siswa')->id,'nipd'=>'nullable|string|max:50|unique:siswas,nipd,'.$this->route('siswa')->id,'nama'=>'required|string|max:255','kelas_id'=>'required|exists:kelas,id','jenis_kelamin'=>'nullable|in:L,P','tanggal_lahir'=>'nullable|date','no_hp_ortu'=>'nullable|string|max:30','status_aktif'=>'sometimes|boolean'];
    }
}
