<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNursingEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        // Solo la descripción es editable; categoría y hora son inmutables.
        return [
            'description' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'La descripción es obligatoria.',
            'description.min'      => 'La descripción debe tener al menos 3 caracteres.',
            'description.max'      => 'La descripción no puede exceder 2000 caracteres.',
        ];
    }
}
