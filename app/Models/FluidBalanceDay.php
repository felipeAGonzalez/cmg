<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FluidBalanceDay extends Model
{
    protected $fillable = [
        'fluid_balance_order_id', 'day_number', 'start_at', 'end_at', 'closed_at',
        'total_inputs_ml', 'total_measured_outputs_ml',
        'total_insensible_losses_ml', 'net_balance_ml',
    ];

    protected function casts(): array
    {
        return [
            'start_at'  => 'datetime',
            'end_at'    => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function fluidBalanceOrder(): BelongsTo
    {
        return $this->belongsTo(FluidBalanceOrder::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FluidBalanceEntry::class)->orderBy('recorded_at');
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /** El día está vencido si ya pasaron 24h desde start_at. */
    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->end_at);
    }

    /** Recalcula y persiste los totales sumando las entries. */
    public function recalculate(): void
    {
        $entries = $this->entries()->get();

        $this->total_inputs_ml = $entries->sum(fn ($e) => $e->totalInputs());
        $this->total_measured_outputs_ml = $entries->sum(fn ($e) => $e->totalMeasuredOutputs());
        $this->total_insensible_losses_ml = $entries->sum('insensible_losses_ml');

        $totalOutputs = $this->total_measured_outputs_ml + $this->total_insensible_losses_ml;
        $this->net_balance_ml = $this->total_inputs_ml - $totalOutputs;

        $this->save();
    }

    /** Cierra el día automáticamente si ya pasaron 24h (lazy, sin scheduler). */
    public function autoCloseIfExpired(): void
    {
        if (! $this->isClosed() && $this->isExpired()) {
            $this->update(['closed_at' => $this->end_at]);
        }
    }
}
