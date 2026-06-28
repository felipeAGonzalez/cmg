<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DischargeTemplate extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description',
        'admission_diagnosis', 'discharge_diagnosis',
        'clinical_summary', 'physical_examination_at_discharge',
        'plan_and_treatment_at_discharge', 'prognosis',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections(): array
    {
        return [
            'admission_diagnosis'              => $this->admission_diagnosis,
            'discharge_diagnosis'              => $this->discharge_diagnosis,
            'clinical_summary'                 => $this->clinical_summary,
            'physical_examination_at_discharge' => $this->physical_examination_at_discharge,
            'plan_and_treatment_at_discharge'  => $this->plan_and_treatment_at_discharge,
            'prognosis'                        => $this->prognosis,
        ];
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($content) => !empty(trim($content ?? '')))
            ->count();
    }
}
