<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGuruRequest extends FormRequest
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
        return ['nama' => 'required|string|max:255', 'nip' => 'required|string|max:50|unique:gurus,nip,'.$this->route('guru')->id, 'email' => 'nullable|email|max:255|unique:gurus,email,'.$this->route('guru')->id, 'phone' => 'nullable|string|max:30', 'is_active' => 'sometimes|boolean', 'roles' => 'array', 'roles.*' => 'exists:roles,id'];
    }
}
