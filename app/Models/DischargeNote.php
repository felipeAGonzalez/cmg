<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DischargeNote extends Model
{
    protected $fillable = [
        'stay_id',
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
        'attending_doctor_id',
        'created_by_id',
        'updated_by_id',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
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

    public function sections(): array
    {
        return [
            'family_history' => $this->family_history,
            'non_pathological_history' => $this->non_pathological_history,
            'pathological_history' => $this->pathological_history,
            'current_illness' => $this->current_illness,
            'general_symptoms' => $this->general_symptoms,
            'physical_examination' => $this->physical_examination,
            'diagnostic_aids' => $this->diagnostic_aids,
            'main_diagnoses' => $this->main_diagnoses,
            'comorbidities' => $this->comorbidities,
            'clinical_plan' => $this->clinical_plan,
            'signature_block' => $this->signature_block,
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
        if (!$doctor) {
            return '';
        }

        $parts = [];

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
}
