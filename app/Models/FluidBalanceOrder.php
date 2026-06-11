<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FluidBalanceOrder extends Model
{
    public const STATUS_ACTIVE     = 'active';
    public const STATUS_SUSPENDED  = 'suspended';
    public const STATUS_DISCHARGED = 'discharged'; // cierre por egreso (calculado)

    /** Motivo que identifica un cierre automático por egreso del paciente. */
    public const DISCHARGE_REASON = 'Finalizada por egreso del paciente.';

    protected $fillable = [
        'stay_id', 'start_date', 'clinical_reason',
        'prescribed_by_id', 'created_by_id', 'updated_by_id',
        'suspended_at', 'suspended_by_id', 'suspension_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'suspended_at' => 'datetime',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(FluidBalanceDay::class)->orderBy('day_number');
    }

    /** Día abierto (sin closed_at) de menor número, o null. */
    public function currentDay(): ?FluidBalanceDay
    {
        return $this->days()
            ->whereNull('closed_at')
            ->orderBy('day_number')
            ->first();
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by_id');
    }

    /** Estado calculado en vivo (nunca se almacena en BD). */
    public function status(): string
    {
        if ($this->suspended_at === null) {
            return self::STATUS_ACTIVE;
        }

        // El cierre por egreso es un cierre natural, no una suspensión humana.
        if ($this->isDischargedReason()) {
            return self::STATUS_DISCHARGED;
        }

        return self::STATUS_SUSPENDED;
    }

    public function isActive(): bool
    {
        return $this->status() === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status() === self::STATUS_SUSPENDED;
    }

    public function isDischarged(): bool
    {
        return $this->status() === self::STATUS_DISCHARGED;
    }

    public function isDischargedReason(): bool
    {
        return $this->suspension_reason === self::DISCHARGE_REASON;
    }

    public function statusLabel(): string
    {
        return [
            self::STATUS_ACTIVE     => 'Activa',
            self::STATUS_SUSPENDED  => 'Suspendida',
            self::STATUS_DISCHARGED => 'Finalizada por egreso',
        ][$this->status()];
    }

    public function statusBadgeClass(): string
    {
        return [
            self::STATUS_ACTIVE     => 'bg-success',
            self::STATUS_SUSPENDED  => 'bg-warning text-dark',
            self::STATUS_DISCHARGED => 'bg-secondary',
        ][$this->status()];
    }

    /**
     * Misma lógica de permisos que MedicationOrder.
     * - Admin: siempre.
     * - Nurse: si el médico prescriptor sigue asignado al stay.
     * - Doctor: solo el prescriptor, y solo mientras siga asignado al stay.
     */
    public function canBeModifiedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isNurse()) {
            return $this->stay->currentDoctors()
                ->where('doctor_id', $this->prescribed_by_id)
                ->exists();
        }

        if ($user->isDoctor()) {
            return $user->id === $this->prescribed_by_id
                && $this->stay->currentDoctors()
                    ->where('doctor_id', $user->id)
                    ->exists();
        }

        return false;
    }

    public function scopeForStay(Builder $query, int $stayId): Builder
    {
        return $query->where('stay_id', $stayId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('suspended_at');
    }
}
