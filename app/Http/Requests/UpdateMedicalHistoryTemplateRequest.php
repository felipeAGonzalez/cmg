<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalHistoryTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor']);
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:150'],
            'description'             => ['nullable', 'string', 'max:500'],
            'family_history'          => ['nullable', 'string', 'max:10000'],
            'non_pathological_history' => ['nullable', 'string', 'max:10000'],
            'pathological_history'    => ['nullable', 'string', 'max:10000'],
            'current_illness'         => ['nullable', 'string', 'max:10000'],
            'general_symptoms'        => ['nullable', 'string', 'max:5000'],
            'physical_examination'    => ['nullable', 'string', 'max:10000'],
            'diagnostic_aids'         => ['nullable', 'string', 'max:10000'],
            'main_diagnoses'          => ['nullable', 'string', 'max:5000'],
            'comorbidities'           => ['nullable', 'string', 'max:5000'],
            'clinical_plan'           => ['nullable', 'string', 'max:15000'],
            'signature_block'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la plantilla es obligatorio.',
            'name.max'      => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
