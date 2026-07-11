<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransfusionNoteTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor']);
    }

    public function rules(): array
    {
        return [
            'name'                       => ['required', 'string', 'max:100'],
            'description'                => ['nullable', 'string', 'max:500'],
            'diagnoses_and_indication'   => ['nullable', 'string', 'max:10000'],
            'compatibility_verification' => ['nullable', 'string', 'max:5000'],
            'evolution_narrative'        => ['nullable', 'string', 'max:10000'],
            'conclusion'                 => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la plantilla es obligatorio.',
            'name.max'      => 'El nombre no puede exceder 100 caracteres.',
        ];
    }
}
