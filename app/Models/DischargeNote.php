<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DischargeNote extends Model
{
    protected $fillable = [
        'stay_id',
        'admission_diagnosis',
        'discharge_diagnosis',
        'clinical_summary',
        'physical_examination_at_discharge',
        'plan_and_treatment_at_discharge',
        'prognosis',
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

    public function isComplete(): bool
    {
        return $this->filledSectionsCount() === 6;
    }

    public function pendingSections(): array
    {
        $sectionConfigs = config('discharge_template_sections', []);
        $pending = [];

        foreach ($this->sections() as $key => $content) {
            if (empty(trim($content ?? ''))) {
                $pending[] = $sectionConfigs[$key]['label'] ?? $key;
            }
        }

        return $pending;
    }

    public function effectiveSignatureBlock(): string
    {
        $doctor = $this->attendingDoctor;
        if (!$doctor) return '';

        $parts = [];
        $parts[] = trim('Dr(a). ' . $doctor->name . ' ' .
            ($doctor->last_name_one ?? '') . ' ' .
            ($doctor->last_name_two ?? ''));

        if (method_exists($doctor, 'specialtiesLabel') && $doctor->specialtiesLabel()) {
            $parts[] = $doctor->specialtiesLabel();
        }

        if (!empty($doctor->professional_license)) {
            $parts[] = 'Céd. Prof. ' . $doctor->professional_license;
        }

        return implode("\n", $parts);
    }
}
