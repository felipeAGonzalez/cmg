<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTriageRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'immediate_alert_loss' => $this->boolean('immediate_alert_loss'),
            'immediate_apnea' => $this->boolean('immediate_apnea'),
            'immediate_no_pulse' => $this->boolean('immediate_no_pulse'),
            'immediate_intubation' => $this->boolean('immediate_intubation'),
            'immediate_angina' => $this->boolean('immediate_angina'),
        ]);
    }

    public function rules(): array
    {
        $scoreA = [0, 5, 10, 15];
        $scoreB = [0, 5, 10];

        return [
            'folio' => ['nullable', 'string', 'max:50'],

            'evaluation_started_at' => ['required', 'date', 'before_or_equal:now'],
            'evaluation_ended_at' => ['nullable', 'date', 'after_or_equal:evaluation_started_at'],

            'heart_rate' => ['nullable', 'integer', 'min:0', 'max:300'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:0', 'max:300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:0', 'max:200'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'temperature' => ['nullable', 'numeric', 'min:25', 'max:45'],
            'oxygen_saturation' => ['nullable', 'integer', 'min:0', 'max:100'],
            'glucose_mg_dl' => ['nullable', 'integer', 'min:0', 'max:1000'],

            'immediate_alert_loss' => ['nullable', 'boolean'],
            'immediate_apnea' => ['nullable', 'boolean'],
            'immediate_no_pulse' => ['nullable', 'boolean'],
            'immediate_intubation' => ['nullable', 'boolean'],
            'immediate_angina' => ['nullable', 'boolean'],

            'trauma_score' => ['required', Rule::in($scoreA)],
            'wound_score' => ['required', Rule::in($scoreA)],
            'respiratory_difficulty_score' => ['required', Rule::in($scoreA)],
            'cyanosis_score' => ['required', Rule::in($scoreA)],
            'paleness_score' => ['required', Rule::in($scoreA)],
            'hemorrhage_score' => ['required', Rule::in($scoreA)],
            'pain_score' => ['required', Rule::in($scoreA)],
            'intoxication_score' => ['required', Rule::in([0, 10, 15])],
            'seizures_score' => ['required', Rule::in([0, 10, 15])],
            'glasgow_score' => ['required', Rule::in($scoreA)],
            'dehydration_score' => ['required', Rule::in($scoreA)],
            'psychosis_score' => ['required', Rule::in([0, 15])],

            'bp_score' => ['required', Rule::in($scoreB)],
            'hr_score' => ['required', Rule::in($scoreB)],
            'rr_score' => ['required', Rule::in($scoreB)],
            'temp_score' => ['required', Rule::in($scoreB)],
            'glucose_score' => ['required', Rule::in($scoreB)],
        ];
    }

    public function messages(): array
    {
        return [
            'evaluation_started_at.required' => 'La hora de inicio de evaluación es obligatoria.',
            'evaluation_started_at.before_or_equal' => 'La hora de inicio no puede ser futura.',
            '*.required' => 'Este campo es obligatorio.',
            '*.in' => 'Valor inválido para este parámetro.',
        ];
    }
}
