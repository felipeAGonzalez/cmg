<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNursingEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'category' => [
                'required',
                Rule::in(array_keys(config('nursing_entry_categories'))),
            ],
            'description' => ['required', 'string', 'min:3', 'max:2000'],
            'recorded_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required'            => 'Selecciona la categoría del registro.',
            'category.in'                  => 'La categoría seleccionada no es válida.',
            'description.required'         => 'La descripción es obligatoria.',
            'description.min'              => 'La descripción debe tener al menos 3 caracteres.',
            'description.max'              => 'La descripción no puede exceder 2000 caracteres.',
            'recorded_at.required'         => 'La hora del registro es obligatoria.',
            'recorded_at.before_or_equal'  => 'La hora no puede ser futura.',
        ];
    }
}
