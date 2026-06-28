<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvolutionNote extends Model
{
    protected $fillable = [
        'stay_id', 'note_datetime',
        'antecedents', 'subjective', 'objective', 'analysis',
        'diagnosis', 'prognosis', 'plan',
        'medications_from', 'medications_to',
        'attending_doctor_id', 'created_by_id', 'updated_by_id',
    ];

    protected $casts = [
        'note_datetime'    => 'datetime',
        'medications_from' => 'datetime',
        'medications_to'   => 'datetime',
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
            'antecedents' => $this->antecedents,
            'subjective'  => $this->subjective,
            'objective'   => $this->objective,
            'analysis'    => $this->analysis,
            'diagnosis'   => $this->diagnosis,
            'prognosis'   => $this->prognosis,
            'plan'        => $this->plan,
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
