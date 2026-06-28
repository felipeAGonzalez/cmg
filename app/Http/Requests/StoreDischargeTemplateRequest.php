<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDischargeTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor']);
    }

    public function rules(): array
    {
        return [
            'name'                             => ['required', 'string', 'max:100'],
            'description'                      => ['nullable', 'string', 'max:500'],
            'admission_diagnosis'              => ['nullable', 'string', 'max:10000'],
            'discharge_diagnosis'              => ['nullable', 'string', 'max:10000'],
            'clinical_summary'                 => ['nullable', 'string', 'max:10000'],
            'physical_examination_at_discharge' => ['nullable', 'string', 'max:10000'],
            'plan_and_treatment_at_discharge'  => ['nullable', 'string', 'max:10000'],
            'prognosis'                        => ['nullable', 'string', 'max:10000'],
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
