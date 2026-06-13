<?php

namespace App\Models;

use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlucoseReading extends Model
{
    protected $fillable = [
        'stay_id', 'glucose_monitoring_order_id', 'recorded_at',
        'shift', 'shift_date', 'value_mg_dl', 'notes', 'recorded_by_id',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(GlucoseMonitoringOrder::class, 'glucose_monitoring_order_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function isEditable(): bool
    {
        return Shift::isSameShift(Carbon::parse($this->recorded_at), now());
    }

    public function shiftLabel(): string
    {
        return Shift::label($this->shift);
    }

    /**
     * Color del badge según rango de glucemia.
     * Hipoglucemia: <70, Normal: 70-180, Hiperglucemia: >180.
     */
    public function rangeBadgeClass(): string
    {
        if ($this->value_mg_dl < 70) {
            return 'bg-warning text-dark';
        }

        if ($this->value_mg_dl > 180) {
            return 'bg-danger';
        }

        return 'bg-success';
    }

    public function scopeForStay(Builder $query, int $stayId): Builder
    {
        return $query->where('stay_id', $stayId)->orderByDesc('recorded_at');
    }
}
