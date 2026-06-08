<?php

namespace App\Models;

use App\Support\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationAdministration extends Model
{
    public const STATUS_ADMINISTERED = 'administered';
    public const STATUS_REFUSED      = 'refused';
    public const STATUS_OMITTED      = 'omitted';

    protected $fillable = [
        'stay_id', 'medication_order_id', 'administered_at', 'shift', 'shift_date',
        'actual_dose', 'status', 'reason', 'observations', 'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'administered_at' => 'datetime',
            'shift_date'      => 'date',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function medicationOrder(): BelongsTo
    {
        return $this->belongsTo(MedicationOrder::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    /**
     * Solo editable/eliminable durante el turno en que se administró.
     * El turno se deriva de administered_at (guardado en shift/shift_date).
     */
    public function isEditable(): bool
    {
        $current = Shift::currentShift();

        return $this->shift === $current['shift']
            && $this->shift_date->equalTo($current['shift_date']);
    }

    public function shiftLabel(): string
    {
        return Shift::label($this->shift);
    }

    public function statusLabel(): string
    {
        return config('administration_statuses')[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return [
            self::STATUS_ADMINISTERED => 'bg-success',
            self::STATUS_REFUSED      => 'bg-danger',
            self::STATUS_OMITTED      => 'bg-secondary',
        ][$this->status] ?? 'bg-secondary';
    }

    public function scopeForStay(Builder $query, int $stayId): Builder
    {
        return $query->where('stay_id', $stayId);
    }
}
