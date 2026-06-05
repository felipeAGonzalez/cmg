<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    protected $fillable = ['number'];

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }

    /**
     * Todas las estancias activas del cuarto (normalmente 1; hasta 2 en caso de
     * nacimiento: madre + recién nacido). Ordenadas por ingreso (la madre primero).
     */
    public function currentStays(): HasMany
    {
        return $this->hasMany(Stay::class)
            ->whereNull('discharge_date')
            ->orderBy('admission_date');
    }

    /**
     * Estancia activa principal del cuarto (la más antigua). Se conserva para
     * compatibilidad con código que asume una sola estancia.
     */
    public function currentStay(): HasOne
    {
        return $this->hasOne(Stay::class)
            ->whereNull('discharge_date')
            ->oldestOfMany('admission_date');
    }

    /** Disponible para un ingreso normal: sin ninguna estancia activa. */
    public function isAvailable(): bool
    {
        return $this->currentStays->isEmpty();
    }

    /**
     * Permite registrar un nacimiento (segundo ocupante) cuando hay exactamente
     * una estancia activa (la madre) y por tanto aún hay espacio.
     */
    public function canRegisterBirth(): bool
    {
        return $this->currentStays->count() === 1;
    }

    public function currentPatient(): ?Patient
    {
        return $this->currentStay?->patient;
    }
}
