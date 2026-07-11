<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransfusionNote extends Model
{
    protected $fillable = [
        'stay_id', 'transfusion_checklist_id',
        'start_datetime', 'end_datetime',
        'diagnoses_and_indication', 'compatibility_verification',
        'evolution_narrative', 'conclusion',
        'pre_ta', 'pre_fc', 'pre_fr', 'pre_temp', 'pre_spo2',
        'post_ta', 'post_fc', 'post_fr', 'post_temp', 'post_spo2',
        'attending_doctor_id', 'created_by_id', 'updated_by_id',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function transfusionChecklist()
    {
        return $this->belongsTo(TransfusionChecklist::class);
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
            'diagnoses_and_indication'  => $this->diagnoses_and_indication,
            'compatibility_verification' => $this->compatibility_verification,
            'evolution_narrative'        => $this->evolution_narrative,
            'conclusion'                 => $this->conclusion,
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
        $doctor = $this->attendingDoctor;
        if (!$doctor) return '';

        $parts = ['Dr(a). ' . trim($doctor->fullName())];

        if (!empty($doctor->specialtiesLabel())) {
            $parts[] = $doctor->specialtiesLabel();
        }

        if (!empty($doctor->professional_license)) {
            $parts[] = 'Céd. Prof. ' . $doctor->professional_license;
        }

        return implode("\n", $parts);
    }
}
