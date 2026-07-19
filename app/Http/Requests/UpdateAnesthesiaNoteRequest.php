<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnesthesiaNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->isDoctor() || $user->isNurse();
    }

    public function rules(): array
    {
        $rules = [
            'post_surgical_note_id'          => ['nullable', 'exists:post_surgical_notes,id'],

            'surgery_urgency'                => ['nullable', 'in:urgencia,electiva'],
            'preop_diagnosis'                => ['nullable', 'string', 'max:10000'],
            'planned_surgery'                => ['nullable', 'string', 'max:5000'],
            'antecedents'                    => ['nullable', 'array'],
            'antecedents.*.has_condition'    => ['nullable', 'boolean'],
            'antecedents.*.evolution_time'   => ['nullable', 'string', 'max:100'],
            'current_medications'            => ['nullable', 'string', 'max:5000'],
            'previous_anesthesias'           => ['nullable', 'string', 'max:5000'],
            'other_antecedents'              => ['nullable', 'string', 'max:5000'],
            'current_illness'                => ['nullable', 'string', 'max:15000'],
            'consciousness_state'            => ['nullable', 'in:consciente,inconsciente,desorientado'],
            'weight_kg'                      => ['nullable', 'string', 'max:10'],
            'height_m'                       => ['nullable', 'string', 'max:10'],
            'exam_ta'                        => ['nullable', 'string', 'max:20'],
            'exam_fc'                        => ['nullable', 'string', 'max:10'],
            'exam_fr'                        => ['nullable', 'string', 'max:10'],
            'exam_temp'                      => ['nullable', 'string', 'max:10'],
            'head_neck_exam'                 => ['nullable', 'string', 'max:5000'],
            'airway_exam'                    => ['nullable', 'string', 'max:5000'],
            'cardiopulmonary_exam'           => ['nullable', 'string', 'max:5000'],
            'abdomen_exam'                   => ['nullable', 'string', 'max:5000'],
            'spine_exam'                     => ['nullable', 'string', 'max:5000'],
            'extremities_exam'               => ['nullable', 'string', 'max:5000'],
            'other_exam'                     => ['nullable', 'string', 'max:5000'],
            'lab_hb'                         => ['nullable', 'string', 'max:20'],
            'lab_hto'                        => ['nullable', 'string', 'max:20'],
            'lab_tp'                         => ['nullable', 'string', 'max:20'],
            'lab_tpt'                        => ['nullable', 'string', 'max:20'],
            'lab_blood_type_rh'              => ['nullable', 'string', 'max:20'],
            'lab_glucose'                    => ['nullable', 'string', 'max:20'],
            'lab_urea'                       => ['nullable', 'string', 'max:20'],
            'lab_creatinine'                 => ['nullable', 'string', 'max:20'],
            'other_labs'                     => ['nullable', 'string', 'max:5000'],
            'cabinet_studies'                => ['nullable', 'string', 'max:5000'],
            'asa_status'                     => ['nullable', 'in:I,II,III,IV,V'],
            'anesthetic_plan'                => ['nullable', 'string', 'max:10000'],
            'preanesthetic_indications'      => ['nullable', 'string', 'max:5000'],
            'postop_diagnosis'               => ['nullable', 'string', 'max:10000'],
            'performed_surgery'              => ['nullable', 'string', 'max:5000'],
            'or_surgeon_user_id'             => ['nullable'],
            'or_surgeon_other_name'          => ['nullable', 'string', 'max:150'],
            'or_assistant_user_id'           => ['nullable'],
            'or_assistant_other_name'        => ['nullable', 'string', 'max:150'],
            'intubation_blade'               => ['nullable', 'string', 'max:50'],
            'intubation_cannula'             => ['nullable', 'string', 'max:50'],
            'intubation_technical_difficulty' => ['nullable', 'boolean'],
            'intubation_difficulty_detail'   => ['nullable', 'string', 'max:255'],
            'ventilation_notes'              => ['nullable', 'string', 'max:5000'],
            'continuous_ecg'                 => ['nullable', 'boolean'],
            'pulse_oximetry'                 => ['nullable', 'boolean'],
            'capnography'                    => ['nullable', 'boolean'],
            'fluids_in_hartmann'             => ['nullable', 'integer', 'min:0'],
            'fluids_in_glucose'              => ['nullable', 'integer', 'min:0'],
            'fluids_in_nacl'                 => ['nullable', 'integer', 'min:0'],
            'fluids_out_diuresis'            => ['nullable', 'integer', 'min:0'],
            'fluids_out_bleeding'            => ['nullable', 'integer', 'min:0'],
            'fluids_out_insensible_losses'   => ['nullable', 'integer', 'min:0'],
            'aldrete_or_exit'                => ['nullable', 'array'],
            'aldrete_or_exit.*'              => ['nullable', 'integer', 'min:0', 'max:2'],
            'regional_anesthesia_type'       => ['nullable', 'string', 'max:100'],
            'regional_needle'                => ['nullable', 'string', 'max:50'],
            'regional_puncture_level'        => ['nullable', 'string', 'max:50'],
            'regional_catheter'              => ['nullable', 'boolean'],
            'regional_agents_administered'   => ['nullable', 'string', 'max:5000'],
            'anesthesia_start'               => ['nullable', 'date_format:Y-m-d\TH:i'],
            'anesthesia_end'                 => ['nullable', 'date_format:Y-m-d\TH:i'],
            'surgery_start'                  => ['nullable', 'date_format:Y-m-d\TH:i'],
            'surgery_end'                    => ['nullable', 'date_format:Y-m-d\TH:i'],
            'anesthetic_time_total'          => ['nullable', 'string', 'max:50'],
            'equipment_review'               => ['nullable', 'string', 'max:5000'],
            'total_dose'                     => ['nullable', 'string', 'max:100'],
            'or_incidents'                   => ['nullable', 'string', 'max:5000'],
            'anesthetic_technique_and_drugs' => ['nullable', 'string', 'max:15000'],
            'blood_fluids_administered'      => ['nullable', 'string', 'max:5000'],
            'incidents_or_accidents'         => ['nullable', 'boolean'],
            'management_plan'                => ['nullable', 'string', 'max:5000'],
            'ucpa_admission_ta'              => ['nullable', 'string', 'max:20'],
            'ucpa_admission_fc'              => ['nullable', 'string', 'max:10'],
            'ucpa_admission_fr'              => ['nullable', 'string', 'max:10'],
            'ucpa_admission_spo2'            => ['nullable', 'string', 'max:10'],
            'aldrete_ucpa_admission'         => ['nullable', 'array'],
            'aldrete_ucpa_admission.*'       => ['nullable', 'integer', 'min:0', 'max:2'],
            'aldrete_ucpa_discharge'         => ['nullable', 'array'],
            'aldrete_ucpa_discharge.*'       => ['nullable', 'integer', 'min:0', 'max:2'],
            'evolution_and_ucpa_discharge'   => ['nullable', 'string', 'max:10000'],
            'ucpa_discharge_ta'              => ['nullable', 'string', 'max:20'],
            'ucpa_discharge_fc'              => ['nullable', 'string', 'max:10'],
            'ucpa_discharge_fr'              => ['nullable', 'string', 'max:10'],
            'ucpa_discharge_spo2'            => ['nullable', 'string', 'max:10'],
            'postop_pain_control'            => ['nullable', 'string', 'max:5000'],
            'discharge_ta'                   => ['nullable', 'string', 'max:20'],
            'discharge_pulse'                => ['nullable', 'string', 'max:10'],
            'discharge_resp'                 => ['nullable', 'string', 'max:10'],
            'discharge_consciousness'        => ['nullable', 'in:consciente,somnoliento,inconsciente'],
            'discharge_nausea'               => ['nullable', 'boolean'],
            'discharge_vomiting'             => ['nullable', 'boolean'],
            'discharge_headache'             => ['nullable', 'boolean'],
            'discharge_diuresis'             => ['nullable', 'string', 'max:100'],
            'discharge_pain'                 => ['nullable', 'string', 'max:100'],
            'discharge_evolution'            => ['nullable', 'string', 'max:5000'],
            'discharge_ambulation'           => ['nullable', 'boolean'],
            'discharge_indications'          => ['nullable', 'string', 'max:5000'],
            'vital_readings'                         => ['nullable', 'array'],
            'vital_readings.*.reading_time'          => ['required_with:vital_readings', 'date_format:H:i'],
            'vital_readings.*.ta_sys'                => ['nullable', 'integer', 'min:0', 'max:300'],
            'vital_readings.*.ta_dia'                => ['nullable', 'integer', 'min:0', 'max:300'],
            'vital_readings.*.fc'                    => ['nullable', 'integer', 'min:0', 'max:300'],
            'vital_readings.*.fr'                    => ['nullable', 'integer', 'min:0', 'max:100'],
            'vital_readings.*.temp'                  => ['nullable', 'numeric', 'min:30', 'max:45'],
            'vital_readings.*.spo2'                  => ['nullable', 'integer', 'min:0', 'max:100'],
            'vital_readings.*.event_marker'          => ['nullable', 'string', 'max:50'],
            'vital_readings.*.hartmann_ml'           => ['nullable', 'integer', 'min:0', 'max:5000'],
            'vital_readings.*.glucose_ml'            => ['nullable', 'integer', 'min:0', 'max:5000'],
            'vital_readings.*.nacl_ml'               => ['nullable', 'integer', 'min:0', 'max:5000'],
        ];

        if (!auth()->user()->isDoctor()) {
            $rules['attending_doctor_id'] = [
                'required',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'doctor')),
            ];
        }

        return $rules;
    }
}
