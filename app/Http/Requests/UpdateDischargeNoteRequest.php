<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDischargeNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    public function rules(): array
    {
        $rules = [
            'admission_diagnosis'              => ['nullable', 'string', 'max:10000'],
            'discharge_diagnosis'              => ['nullable', 'string', 'max:10000'],
            'clinical_summary'                 => ['nullable', 'string', 'max:15000'],
            'physical_examination_at_discharge' => ['nullable', 'string', 'max:10000'],
            'plan_and_treatment_at_discharge'  => ['nullable', 'string', 'max:15000'],
            'prognosis'                        => ['nullable', 'string', 'max:5000'],
        ];

        if (auth()->user() && !auth()->user()->isDoctor()) {
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
