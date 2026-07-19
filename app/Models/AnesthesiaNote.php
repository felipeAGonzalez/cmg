<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnesthesiaNote extends Model
{
    protected $fillable = [
        'stay_id', 'post_surgical_note_id',
        'surgery_urgency', 'preop_diagnosis', 'planned_surgery',
        'antecedents', 'current_medications', 'previous_anesthesias', 'other_antecedents',
        'current_illness',
        'consciousness_state', 'weight_kg', 'height_m',
        'exam_ta', 'exam_fc', 'exam_fr', 'exam_temp',
        'head_neck_exam', 'airway_exam', 'cardiopulmonary_exam', 'abdomen_exam',
        'spine_exam', 'extremities_exam', 'other_exam',
        'lab_hb', 'lab_hto', 'lab_tp', 'lab_tpt', 'lab_blood_type_rh',
        'lab_glucose', 'lab_urea', 'lab_creatinine', 'other_labs', 'cabinet_studies',
        'asa_status', 'anesthetic_plan', 'preanesthetic_indications',
        'postop_diagnosis', 'performed_surgery',
        'or_surgeon_user_id', 'or_surgeon_other_name',
        'or_assistant_user_id', 'or_assistant_other_name',
        'intubation_blade', 'intubation_cannula',
        'intubation_technical_difficulty', 'intubation_difficulty_detail',
        'ventilation_notes', 'continuous_ecg', 'pulse_oximetry', 'capnography',
        'fluids_in_hartmann', 'fluids_in_glucose', 'fluids_in_nacl',
        'fluids_out_diuresis', 'fluids_out_bleeding', 'fluids_out_insensible_losses',
        'aldrete_or_exit',
        'regional_anesthesia_type', 'regional_needle', 'regional_puncture_level',
        'regional_catheter', 'regional_agents_administered',
        'anesthesia_start', 'anesthesia_end', 'surgery_start', 'surgery_end',
        'anesthetic_time_total', 'equipment_review', 'total_dose', 'or_incidents',
        'anesthetic_technique_and_drugs', 'blood_fluids_administered',
        'incidents_or_accidents', 'management_plan',
        'ucpa_admission_ta', 'ucpa_admission_fc', 'ucpa_admission_fr', 'ucpa_admission_spo2',
        'aldrete_ucpa_admission', 'aldrete_ucpa_discharge',
        'evolution_and_ucpa_discharge',
        'ucpa_discharge_ta', 'ucpa_discharge_fc', 'ucpa_discharge_fr', 'ucpa_discharge_spo2',
        'postop_pain_control',
        'discharge_ta', 'discharge_pulse', 'discharge_resp', 'discharge_consciousness',
        'discharge_nausea', 'discharge_vomiting', 'discharge_headache',
        'discharge_diuresis', 'discharge_pain', 'discharge_evolution', 'discharge_ambulation',
        'discharge_indications',
        'attending_doctor_id', 'created_by_id', 'updated_by_id',
    ];

    protected $casts = [
        'antecedents'                  => 'array',
        'aldrete_or_exit'              => 'array',
        'aldrete_ucpa_admission'       => 'array',
        'aldrete_ucpa_discharge'       => 'array',
        'anesthesia_start'             => 'datetime',
        'anesthesia_end'               => 'datetime',
        'surgery_start'                => 'datetime',
        'surgery_end'                  => 'datetime',
        'intubation_technical_difficulty' => 'boolean',
        'continuous_ecg'               => 'boolean',
        'pulse_oximetry'               => 'boolean',
        'capnography'                  => 'boolean',
        'regional_catheter'            => 'boolean',
        'incidents_or_accidents'       => 'boolean',
        'discharge_nausea'             => 'boolean',
        'discharge_vomiting'           => 'boolean',
        'discharge_headache'           => 'boolean',
        'discharge_ambulation'         => 'boolean',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function postSurgicalNote()
    {
        return $this->belongsTo(PostSurgicalNote::class);
    }

    public function vitalReadings()
    {
        return $this->hasMany(AnesthesiaNoteVitalReading::class)->orderBy('sort_order');
    }

    public function orSurgeonUser()
    {
        return $this->belongsTo(User::class, 'or_surgeon_user_id');
    }

    public function orAssistantUser()
    {
        return $this->belongsTo(User::class, 'or_assistant_user_id');
    }

    public function attendingDoctor()
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function orSurgeonName(): string
    {
        if ($this->or_surgeon_user_id && $this->orSurgeonUser) {
            return 'Dr(a). ' . $this->orSurgeonUser->name . ' ' . ($this->orSurgeonUser->last_name_one ?? '');
        }
        if (!empty($this->or_surgeon_other_name)) {
            return $this->or_surgeon_other_name;
        }
        if ($this->postSurgicalNote) {
            return $this->postSurgicalNote->surgeonName();
        }
        return '—';
    }

    public function orAssistantName(): string
    {
        if ($this->or_assistant_user_id && $this->orAssistantUser) {
            return 'Dr(a). ' . $this->orAssistantUser->name . ' ' . ($this->orAssistantUser->last_name_one ?? '');
        }
        if (!empty($this->or_assistant_other_name)) {
            return $this->or_assistant_other_name;
        }
        if ($this->postSurgicalNote) {
            return $this->postSurgicalNote->assistantName();
        }
        return '—';
    }

    public function aldreteTotal(?array $scale): ?int
    {
        if (!$scale) return null;
        $keys = ['activity', 'respiration', 'circulation', 'consciousness', 'saturation'];
        $sum = 0;
        foreach ($keys as $k) {
            if (!isset($scale[$k])) return null;
            $sum += (int) $scale[$k];
        }
        return $sum;
    }

    public function effectiveSignatureBlock(): string
    {
        $doctor = $this->attendingDoctor;
        if (!$doctor) return '';
        $parts = [];
        $parts[] = trim('Dr(a). ' . $doctor->name . ' ' . ($doctor->last_name_one ?? '') . ' ' . ($doctor->last_name_two ?? ''));
        if (method_exists($doctor, 'specialtiesLabel') && $doctor->specialtiesLabel()) {
            $parts[] = $doctor->specialtiesLabel();
        }
        if (!empty($doctor->professional_license)) {
            $parts[] = 'Céd. Prof. ' . $doctor->professional_license;
        }
        return implode("\n", $parts);
    }
}
