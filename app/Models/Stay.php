<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stay extends Model
{
    protected $fillable = [
        'patient_id',
        'room_id',
        'diagnosis',
        'admission_date',
        'discharge_date',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'datetime',
            'discharge_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function stayDoctors(): HasMany
    {
        return $this->hasMany(StayDoctor::class);
    }

    public function currentDoctors(): HasMany
    {
        return $this->hasMany(StayDoctor::class)->whereNull('removed_at');
    }

    public function roomTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class);
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(StayInstruction::class)->latest();
    }

    public function isActive(): bool
    {
        return $this->discharge_date === null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('discharge_date');
    }

    public function scopeDischarged(Builder $query): Builder
    {
        return $query->whereNotNull('discharge_date');
    }
}
