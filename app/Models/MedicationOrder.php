<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationOrder extends Model
{
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_FINISHED  = 'finished';

    protected $fillable = [
        'stay_id', 'medication_name', 'dose', 'route', 'route_other',
        'frequency', 'frequency_other', 'start_date', 'duration_days',
        'indications', 'prescribed_by_id', 'created_by_id', 'updated_by_id',
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

    public function administrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class)->orderByDesc('administered_at');
    }

    /** Últimas N administraciones (más recientes primero). */
    public function recentAdministrations(int $limit = 5): Collection
    {
        return $this->administrations()->with('recordedBy')->limit($limit)->get();
    }

    public function administrationsCount(): int
    {
        return $this->administrations()->count();
    }

    /** Estado calculado en vivo (nunca se almacena en BD). */
    public function status(): string
    {
        if ($this->suspended_at !== null) {
            return self::STATUS_SUSPENDED;
        }

        if ($this->isExpired()) {
            return self::STATUS_FINISHED;
        }

        return self::STATUS_ACTIVE;
    }

    public function isActive(): bool
    {
        return $this->status() === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status() === self::STATUS_SUSPENDED;
    }

    public function isFinished(): bool
    {
        return $this->status() === self::STATUS_FINISHED;
    }

    /** Días transcurridos desde start_date hasta hoy (incluyente del día 1). */
    public function daysElapsed(): int
    {
        return (int) max(1, $this->start_date->copy()->startOfDay()
            ->diffInDays(now()->startOfDay()) + 1);
    }

    /** Días restantes hasta finalizar. Null si no hay duración definida. */
    public function daysRemaining(): ?int
    {
        if ($this->duration_days === null) {
            return null;
        }

        $endDate = $this->start_date->copy()->addDays($this->duration_days - 1);

        return (int) max(0, now()->startOfDay()->diffInDays($endDate->startOfDay(), false));
    }

    /** ¿Ya pasó la duración programada? */
    public function isExpired(): bool
    {
        if ($this->duration_days === null) {
            return false;
        }

        $endDate = $this->start_date->copy()->addDays($this->duration_days - 1);

        return now()->startOfDay()->greaterThan($endDate->startOfDay());
    }

    /** Texto 'X/Y días' de progreso. Null si sin duración definida. */
    public function progressLabel(): ?string
    {
        if ($this->duration_days === null) {
            return null;
        }

        $elapsed = min($this->daysElapsed(), $this->duration_days);

        return "{$elapsed}/{$this->duration_days} días";
    }

    /** Fecha de finalización programada. Null si sin duración. */
    public function endDate(): ?\Carbon\Carbon
    {
        if ($this->duration_days === null) {
            return null;
        }

        return $this->start_date->copy()->addDays($this->duration_days - 1);
    }

    public function routeLabel(): string
    {
        if ($this->route === 'other') {
            return $this->route_other ?? 'Otra';
        }

        return config('medication_routes')[$this->route] ?? $this->route;
    }

    public function frequencyLabel(): string
    {
        if ($this->frequency === 'other') {
            return $this->frequency_other ?? 'Otra';
        }

        return config('medication_frequencies')[$this->frequency] ?? $this->frequency;
    }

    public function statusLabel(): string
    {
        return [
            self::STATUS_ACTIVE    => 'Activa',
            self::STATUS_SUSPENDED => 'Suspendida',
            self::STATUS_FINISHED  => 'Finalizada',
        ][$this->status()];
    }

    public function statusBadgeClass(): string
    {
        return [
            self::STATUS_ACTIVE    => 'bg-success',
            self::STATUS_SUSPENDED => 'bg-warning text-dark',
            self::STATUS_FINISHED  => 'bg-secondary',
        ][$this->status()];
    }

    /**
     * ¿Quién puede editar/suspender? El doctor que prescribió, una enfermera
     * (si ese doctor sigue asignado al stay) o un admin. Otros doctores
     * asignados solo pueden ver.
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
            // Regla de negocio: el médico prescriptor puede modificar solo
            // mientras siga asignado a la estancia. Si se le retira la
            // asignación, pierde la capacidad de modificar sus prescripciones.
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

    public function scopeNotSuspended(Builder $query): Builder
    {
        return $query->whereNull('suspended_at');
    }
}
