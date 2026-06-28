<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalHistoryTemplate extends Model
{
    protected $table = 'medical_history_templates';

    protected $fillable = [
        'owner_id', 'name', 'description',
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
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
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

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('owner_id', $userId);
    }
}
