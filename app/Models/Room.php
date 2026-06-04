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

    public function currentStay(): HasOne
    {
        return $this->hasOne(Stay::class)->whereNull('discharge_date');
    }

    public function isAvailable(): bool
    {
        return $this->currentStay === null;
    }

    public function currentPatient(): ?Patient
    {
        return $this->currentStay?->patient;
    }
}
