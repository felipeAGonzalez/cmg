<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriageRecord extends Model
{
    public const COLOR_BLUE = 'blue';
    public const COLOR_GREEN = 'green';
    public const COLOR_YELLOW = 'yellow';
    public const COLOR_ORANGE = 'orange';
    public const COLOR_RED = 'red';

    public const DISPOSITION_PENDING = 'pending';
    public const DISPOSITION_HOSPITALIZED = 'hospitalized';
    public const DISPOSITION_AMBULATORY = 'ambulatory';
    public const DISPOSITION_REFUSED = 'refused';
    public const DISPOSITION_REFERRED = 'referred';

    protected $fillable = [
        'patient_id', 'folio',
        'evaluation_started_at', 'evaluation_ended_at',
        'heart_rate', 'blood_pressure_systolic', 'blood_pressure_diastolic',
        'respiratory_rate', 'temperature', 'oxygen_saturation', 'glucose_mg_dl',
        'immediate_alert_loss', 'immediate_apnea', 'immediate_no_pulse',
        'immediate_intubation', 'immediate_angina',
        'trauma_score', 'wound_score', 'respiratory_difficulty_score',
        'cyanosis_score', 'paleness_score', 'hemorrhage_score',
        'pain_score', 'intoxication_score', 'seizures_score',
        'glasgow_score', 'dehydration_score', 'psychosis_score',
        'bp_score', 'hr_score', 'rr_score', 'temp_score', 'glucose_score',
        'sum_partial_a', 'sum_partial_b', 'total_score', 'color',
        'disposition', 'disposition_at', 'disposition_by_id',
        'performed_by_id',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_started_at' => 'datetime',
            'evaluation_ended_at' => 'datetime',
            'disposition_at' => 'datetime',
            'immediate_alert_loss' => 'boolean',
            'immediate_apnea' => 'boolean',
            'immediate_no_pulse' => 'boolean',
            'immediate_intubation' => 'boolean',
            'immediate_angina' => 'boolean',
            'temperature' => 'decimal:1',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_id');
    }

    public function dispositionBy()
    {
        return $this->belongsTo(User::class, 'disposition_by_id');
    }

    public function hasImmediateAlert(): bool
    {
        return $this->immediate_alert_loss
            || $this->immediate_apnea
            || $this->immediate_no_pulse
            || $this->immediate_intubation
            || $this->immediate_angina;
    }

    public function isPending(): bool
    {
        return $this->disposition === self::DISPOSITION_PENDING;
    }

    public function colorLabel(): string
    {
        return [
            self::COLOR_BLUE => 'Azul',
            self::COLOR_GREEN => 'Verde',
            self::COLOR_YELLOW => 'Amarillo',
            self::COLOR_ORANGE => 'Naranja',
            self::COLOR_RED => 'Rojo',
        ][$this->color] ?? 'Sin clasificar';
    }

    public function decisionLabel(): string
    {
        return [
            self::COLOR_BLUE => 'Sin urgencia',
            self::COLOR_GREEN => 'Urgencia menor',
            self::COLOR_YELLOW => 'Urgencia',
            self::COLOR_ORANGE => 'Emergencia',
            self::COLOR_RED => 'Reanimación',
        ][$this->color] ?? '—';
    }

    public function attentionTimeLabel(): string
    {
        return [
            self::COLOR_BLUE => '121-240 min',
            self::COLOR_GREEN => '61-120 min',
            self::COLOR_YELLOW => '30-60 min',
            self::COLOR_ORANGE => '10 min',
            self::COLOR_RED => 'Inmediato',
        ][$this->color] ?? '—';
    }

    public function siteLabel(): string
    {
        return [
            self::COLOR_BLUE => 'Consultorio',
            self::COLOR_GREEN => 'Primer contacto',
            self::COLOR_YELLOW => 'Observación',
            self::COLOR_ORANGE => 'Estabilización',
            self::COLOR_RED => 'Choque',
        ][$this->color] ?? '—';
    }

    public function colorBadgeClass(): string
    {
        return [
            self::COLOR_BLUE => 'bg-primary',
            self::COLOR_GREEN => 'bg-success',
            self::COLOR_YELLOW => 'bg-warning text-dark',
            self::COLOR_ORANGE => 'bg-orange text-dark',
            self::COLOR_RED => 'bg-danger',
        ][$this->color] ?? 'bg-secondary';
    }

    public function dispositionLabel(): string
    {
        return [
            self::DISPOSITION_PENDING => 'Pendiente',
            self::DISPOSITION_HOSPITALIZED => 'Hospitalizado',
            self::DISPOSITION_AMBULATORY => 'Ambulatorio',
            self::DISPOSITION_REFUSED => 'Rechazó atención',
            self::DISPOSITION_REFERRED => 'Referido',
        ][$this->disposition] ?? $this->disposition;
    }

    public function dispositionBadgeClass(): string
    {
        return [
            self::DISPOSITION_PENDING => 'bg-secondary',
            self::DISPOSITION_HOSPITALIZED => 'bg-info',
            self::DISPOSITION_AMBULATORY => 'bg-success',
            self::DISPOSITION_REFUSED => 'bg-warning text-dark',
            self::DISPOSITION_REFERRED => 'bg-primary',
        ][$this->disposition] ?? 'bg-secondary';
    }

    public function suggestedDestination(): string
    {
        return in_array($this->color, [self::COLOR_BLUE, self::COLOR_GREEN])
            ? 'ambulatory'
            : 'hospitalization';
    }

    public function suggestedDestinationLabel(): string
    {
        return $this->suggestedDestination() === 'ambulatory'
            ? 'Atención ambulatoria'
            : 'Hospitalización';
    }

    public function scopePending($query)
    {
        return $query->where('disposition', self::DISPOSITION_PENDING);
    }

    public function scopeOrderedForWaitingRoom($query)
    {
        return $query
            ->orderByRaw("FIELD(color, 'red', 'orange', 'yellow', 'green', 'blue')")
            ->orderBy('evaluation_started_at', 'asc');
    }
}
