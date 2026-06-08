<?php

namespace App\Models;

use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSignReading extends Model
{
    protected $fillable = [
        'stay_id',
        'recorded_at',
        'shift',
        'shift_date',
        'heart_rate',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'respiratory_rate',
        'temperature',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'shift_date'  => 'date',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Solo editable durante el mismo turno en que se registró. */
    public function isEditable(): bool
    {
        return Shift::isSameShift(Carbon::parse($this->recorded_at), now());
    }

    public function shiftLabel(): string
    {
        return Shift::label($this->shift);
    }

    public function bloodPressureFormatted(): ?string
    {
        if (! $this->blood_pressure_systolic && ! $this->blood_pressure_diastolic) {
            return null;
        }

        return ($this->blood_pressure_systolic ?? '—')
            . '/'
            . ($this->blood_pressure_diastolic ?? '—');
    }

    public function scopeForStay(Builder $query, int $stayId): Builder
    {
        return $query->where('stay_id', $stayId)->orderBy('recorded_at');
    }
}
