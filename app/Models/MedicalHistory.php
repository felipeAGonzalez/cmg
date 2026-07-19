<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    protected $fillable = [
        'stay_id',
        // Modo
        'mode',
        // Modo completo (11 secciones originales)
        'family_history',
        'non_pathological_history',
        'pathological_history',
        'current_illness',
        'general_symptoms',
        'physical_examination',
        'diagnostic_aids',
        'main_diagnoses',
        'comorbidities',
        'clinical_plan',
        'signature_block',
        // Modo simple — interrogatorio
        'simple_interrogation_type',
        // Modo simple — heredofamiliares
        'simple_heredo_father',
        'simple_heredo_mother',
        'simple_heredo_other',
        // Modo simple — no patológicos
        'simple_origin',
        'simple_resident_of',
        'simple_occupation',
        'simple_education',
        'simple_housing_type',
        'simple_housing_other',
        'simple_marital_status',
        'simple_marital_status_other',
        'simple_diet',
        'simple_religion',
        'simple_blood_type_rh',
        'simple_hygiene',
        'simple_non_pathological_checks',
        'simple_non_pathological_other',
        // Modo simple — patológicos
        'simple_pathological_checks',
        'simple_pathological_other',
        'simple_anesthetics_history',
        // Modo simple — gineco-obstétricos
        'simple_gyneco_history',
        'simple_gyneco_vaccines',
        // Modo simple — padecimiento actual
        'simple_current_illness',
        // Modo simple — revisión por aparatos y sistemas
        'simple_review_of_systems',
        // Modo simple — dolor
        'simple_pain_eva_score',
        'simple_pain_wongbaker_score',
        'simple_pain_type',
        'simple_pain_region',
        'simple_pain_duration',
        'simple_pain_associated_signs',
        'simple_pain_associated_factors',
        // Modo simple — exploración física
        'simple_exam_ta',
        'simple_exam_pulse',
        'simple_exam_fc',
        'simple_exam_fr',
        'simple_exam_temp',
        'simple_exam_by_system',
        // Modo simple — cierre
        'simple_lab_studies',
        'simple_diagnosis',
        'simple_therapeutics',
        'simple_prognosis',
        'simple_elaboration_datetime',
        'elaborated_by_id',
        // Claves foráneas de auditoría
        'attending_doctor_id',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'simple_non_pathological_checks' => 'array',
        'simple_pathological_checks'     => 'array',
        'simple_gyneco_history'          => 'array',
        'simple_gyneco_vaccines'         => 'array',
        'simple_review_of_systems'       => 'array',
        'simple_pain_associated_signs'   => 'array',
        'simple_exam_by_system'          => 'array',
        'simple_elaboration_datetime'    => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function stay()            { return $this->belongsTo(Stay::class); }
    public function attendingDoctor() { return $this->belongsTo(User::class, 'attending_doctor_id'); }
    public function createdBy()       { return $this->belongsTo(User::class, 'created_by_id'); }
    public function updatedBy()       { return $this->belongsTo(User::class, 'updated_by_id'); }
    public function elaboratedBy()    { return $this->belongsTo(User::class, 'elaborated_by_id'); }

    // ── Helpers de modo completo (sin cambios) ──────────────────────────────

    public function sections(): array
    {
        return [
            'family_history'           => $this->family_history,
            'non_pathological_history' => $this->non_pathological_history,
            'pathological_history'     => $this->pathological_history,
            'current_illness'          => $this->current_illness,
            'general_symptoms'         => $this->general_symptoms,
            'physical_examination'     => $this->physical_examination,
            'diagnostic_aids'          => $this->diagnostic_aids,
            'main_diagnoses'           => $this->main_diagnoses,
            'comorbidities'            => $this->comorbidities,
            'clinical_plan'            => $this->clinical_plan,
            'signature_block'          => $this->signature_block,
        ];
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($content) => !empty(trim($content ?? '')))
            ->count();
    }

    public function effectiveSignatureBlock(): string
    {
        if (!empty(trim($this->signature_block ?? ''))) {
            return $this->signature_block;
        }

        $doctor = $this->attendingDoctor;
        if (!$doctor) return '';

        $parts   = [];
        $parts[] = trim('Dr(a). ' . $doctor->name . ' ' .
            ($doctor->last_name_one ?? '') . ' ' .
            ($doctor->last_name_two ?? ''));

        if ($doctor->specialtiesLabel()) {
            $parts[] = $doctor->specialtiesLabel();
        }

        if (!empty($doctor->professional_license)) {
            $parts[] = 'Céd. Prof. ' . $doctor->professional_license;
        }

        return implode("\n", $parts);
    }

    // ── Helpers de modo simple ──────────────────────────────────────────────

    public function isSimpleMode(): bool
    {
        return $this->mode === 'simple';
    }

    public function painScaleSummary(): ?string
    {
        $parts = [];
        if ($this->simple_pain_eva_score !== null)
            $parts[] = "EVA: {$this->simple_pain_eva_score}/10";
        if ($this->simple_pain_wongbaker_score !== null)
            $parts[] = "Wong-Baker: {$this->simple_pain_wongbaker_score}/10";
        return $parts ? implode(' · ', $parts) : null;
    }
}
