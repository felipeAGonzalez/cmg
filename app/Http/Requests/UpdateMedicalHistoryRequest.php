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

            // ── Modo ────────────────────────────────────────────────────────
            'mode'                           => ['required', 'in:complete,simple'],

            // ── Modo simple ─────────────────────────────────────────────────
            'simple_interrogation_type'      => ['nullable', 'in:directo,indirecto,diferido'],
            'simple_heredo_father'           => ['nullable', 'string', 'max:2000'],
            'simple_heredo_mother'           => ['nullable', 'string', 'max:2000'],
            'simple_heredo_other'            => ['nullable', 'string', 'max:2000'],
            'simple_origin'                  => ['nullable', 'string', 'max:150'],
            'simple_resident_of'             => ['nullable', 'string', 'max:150'],
            'simple_occupation'              => ['nullable', 'string', 'max:150'],
            'simple_education'               => ['nullable', 'string', 'max:150'],
            'simple_housing_type'            => ['nullable', 'in:propia,rentada,otro'],
            'simple_housing_other'           => ['nullable', 'string', 'max:150'],
            'simple_marital_status'          => ['nullable', 'in:soltero,casado,otro'],
            'simple_marital_status_other'    => ['nullable', 'string', 'max:150'],
            'simple_diet'                    => ['nullable', 'string', 'max:255'],
            'simple_religion'                => ['nullable', 'string', 'max:100'],
            'simple_blood_type_rh'           => ['nullable', 'string', 'max:20'],
            'simple_hygiene'                 => ['nullable', 'string', 'max:2000'],
            'simple_non_pathological_checks' => ['nullable', 'array'],
            'simple_non_pathological_other'  => ['nullable', 'string', 'max:2000'],
            'simple_pathological_checks'     => ['nullable', 'array'],
            'simple_pathological_other'      => ['nullable', 'string', 'max:2000'],
            'simple_anesthetics_history'     => ['nullable', 'string', 'max:2000'],
            'simple_gyneco_history'          => ['nullable', 'array'],
            'simple_gyneco_vaccines'         => ['nullable', 'array'],
            'simple_current_illness'         => ['nullable', 'string', 'max:10000'],
            'simple_review_of_systems'       => ['nullable', 'array'],
            'simple_pain_eva_score'          => ['nullable', 'integer', 'min:0', 'max:10'],
            'simple_pain_wongbaker_score'    => ['nullable', 'integer', 'min:0', 'max:10'],
            'simple_pain_type'               => ['nullable', 'in:somatico,visceral,neuropatico'],
            'simple_pain_region'             => ['nullable', 'string', 'max:150'],
            'simple_pain_duration'           => ['nullable', 'in:continuo,intermitente'],
            'simple_pain_associated_signs'   => ['nullable', 'array'],
            'simple_pain_associated_factors' => ['nullable', 'string', 'max:2000'],
            'simple_exam_ta'                 => ['nullable', 'string', 'max:20'],
            'simple_exam_pulse'              => ['nullable', 'string', 'max:10'],
            'simple_exam_fc'                 => ['nullable', 'string', 'max:10'],
            'simple_exam_fr'                 => ['nullable', 'string', 'max:10'],
            'simple_exam_temp'               => ['nullable', 'string', 'max:10'],
            'simple_exam_by_system'          => ['nullable', 'array'],
            'simple_lab_studies'             => ['nullable', 'string', 'max:5000'],
            'simple_diagnosis'               => ['nullable', 'string', 'max:5000'],
            'simple_therapeutics'            => ['nullable', 'string', 'max:5000'],
            'simple_prognosis'               => ['nullable', 'string', 'max:2000'],
            'simple_elaboration_datetime'    => ['nullable', 'date'],
            'elaborated_by_id'               => ['nullable', 'exists:users,id'],
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
