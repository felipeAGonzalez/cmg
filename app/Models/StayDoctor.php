<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayDoctor extends Model
{
    protected $fillable = [
        'stay_id',
        'doctor_id',
        'specialty',
        'assigned_at',
        'removed_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'removed_at'  => 'datetime',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('removed_at');
    }
}
