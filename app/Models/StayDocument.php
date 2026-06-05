<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayDocument extends Model
{
    public const STATUS_PENDING        = 'pending';
    public const STATUS_COMPLETED      = 'completed';
    public const STATUS_NOT_APPLICABLE = 'not_applicable';

    protected $fillable = [
        'stay_id',
        'document_id',
        'status',
        'form_data',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'form_data'    => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED      => 'Completado',
            self::STATUS_NOT_APPLICABLE => 'No aplica',
            default                     => 'Pendiente',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED      => 'bg-success',
            self::STATUS_NOT_APPLICABLE => 'bg-secondary',
            default                     => 'bg-warning text-dark',
        };
    }

    public function scopeForStay(Builder $query, int $stayId): Builder
    {
        return $query->where('stay_id', $stayId);
    }
}
