<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMasterPelanggaranRequest extends FormRequest
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
            'nama_pelanggaran' => 'required|string|max:255',
            'pasal_id' => 'required|exists:pasals,id',
            'jenis_id' => 'required|exists:jenis_pelanggarans,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
