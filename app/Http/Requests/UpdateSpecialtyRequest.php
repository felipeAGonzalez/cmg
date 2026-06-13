<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('specialties', 'name')->ignore($this->route('specialty')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la especialidad es obligatorio.',
            'name.max'      => 'El nombre no puede superar 100 caracteres.',
            'name.unique'   => 'Ya existe una especialidad con ese nombre.',
        ];
    }
}
