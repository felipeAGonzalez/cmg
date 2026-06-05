<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'type',
        'is_universal',
        'available_on_discharge',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_universal'           => 'boolean',
            'available_on_discharge' => 'boolean',
            'is_active'              => 'boolean',
        ];
    }

    public function stayDocuments(): HasMany
    {
        return $this->hasMany(StayDocument::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeUniversal(Builder $query): Builder
    {
        return $query->where('is_universal', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
