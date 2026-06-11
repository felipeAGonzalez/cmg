<?php

namespace App\Models;

use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FluidBalanceEntry extends Model
{
    protected $fillable = [
        'fluid_balance_day_id', 'recorded_at', 'shift', 'shift_date',
        'oral_ml', 'iv_solution_ml', 'blood_ml', 'plasma_ml', 'sonda_ml',
        'other_inputs_ml',
        'urine_ml', 'evacuation_ml', 'vomit_ml', 'hemorrhage_ml',
        'suction_ml', 'canalization_ml',
        'insensible_losses_ml', 'formula_used', 'temperature_at_entry',
        'weight_at_entry', 'hours_since_previous',
        'observation', 'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at'          => 'datetime',
            'shift_date'           => 'date',
            'temperature_at_entry' => 'decimal:2',
            'weight_at_entry'      => 'decimal:2',
            'hours_since_previous' => 'decimal:2',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(FluidBalanceDay::class, 'fluid_balance_day_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    /** Solo editable/eliminable durante el turno en que se registró. */
    public function isEditable(): bool
    {
        return Shift::isSameShift(
            Carbon::parse($this->recorded_at),
            now()
        );
    }

    public function shiftLabel(): string
    {
        return Shift::label($this->shift);
    }

    public function totalInputs(): int
    {
        return $this->oral_ml + $this->iv_solution_ml + $this->blood_ml
            + $this->plasma_ml + $this->sonda_ml + $this->other_inputs_ml;
    }

    public function totalMeasuredOutputs(): int
    {
        return $this->urine_ml + $this->evacuation_ml + $this->vomit_ml
            + $this->hemorrhage_ml + $this->suction_ml + $this->canalization_ml;
    }

    public function totalOutputs(): int
    {
        return $this->totalMeasuredOutputs() + $this->insensible_losses_ml;
    }

    public function netBalance(): int
    {
        return $this->totalInputs() - $this->totalOutputs();
    }

    public function formulaLabel(): string
    {
        if ($this->formula_used === null) {
            return '—';
        }

        return config('fluid_balance_formulas')[$this->formula_used] ?? $this->formula_used;
    }
}
