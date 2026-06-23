<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    public function rules(): array
    {
        $rules = [
            'family_history' => ['nullable', 'string', 'max:10000'],
            'non_pathological_history' => ['nullable', 'string', 'max:10000'],
            'pathological_history' => ['nullable', 'string', 'max:10000'],
            'current_illness' => ['nullable', 'string', 'max:10000'],
            'general_symptoms' => ['nullable', 'string', 'max:5000'],
            'physical_examination' => ['nullable', 'string', 'max:10000'],
            'diagnostic_aids' => ['nullable', 'string', 'max:10000'],
            'main_diagnoses' => ['nullable', 'string', 'max:5000'],
            'comorbidities' => ['nullable', 'string', 'max:5000'],
            'clinical_plan' => ['nullable', 'string', 'max:15000'],
            'signature_block' => ['nullable', 'string', 'max:500'],
        ];

        if (!auth()->user()->isDoctor()) {
            $rules['attending_doctor_id'] = [
                'required',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'doctor')),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'attending_doctor_id.required' => 'Selecciona el médico tratante.',
        ];
    }
}
