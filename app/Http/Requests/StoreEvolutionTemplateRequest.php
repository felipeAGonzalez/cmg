<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvolutionTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor']);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'antecedents' => ['nullable', 'string', 'max:10000'],
            'subjective'  => ['nullable', 'string', 'max:10000'],
            'objective'   => ['nullable', 'string', 'max:10000'],
            'analysis'    => ['nullable', 'string', 'max:10000'],
            'diagnosis'   => ['nullable', 'string', 'max:10000'],
            'prognosis'   => ['nullable', 'string', 'max:10000'],
            'plan'        => ['nullable', 'string', 'max:10000'],
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
