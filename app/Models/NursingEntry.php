<?php

namespace App\Models;

use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingEntry extends Model
{
    public const CATEGORY_TREATMENT = 'treatment';
    public const CATEGORY_SYMPTOM = 'symptom';
    public const CATEGORY_ASSISTIVE_MEASURE = 'assistive_measure';
    public const CATEGORY_EVOLUTION_NOTE = 'evolution_note';
    public const CATEGORY_OBSERVATION = 'observation';

    protected $fillable = [
        'stay_id',
        'category',
        'description',
        'recorded_at',
        'shift',
        'shift_date',
        'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'shift_date'  => 'date',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
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

    /** @return array{label: string, icon: string, badge_class: string, description: string} */
    public function categoryConfig(): array
    {
        return config('nursing_entry_categories')[$this->category] ?? [
            'label'       => $this->category,
            'icon'        => 'bi-circle',
            'badge_class' => 'bg-secondary',
            'description' => '',
        ];
    }

    public function categoryLabel(): string
    {
        return $this->categoryConfig()['label'];
    }

    public function categoryIcon(): string
    {
        return $this->categoryConfig()['icon'];
    }

    public function categoryBadgeClass(): string
    {
        return $this->categoryConfig()['badge_class'];
    }

    public function scopeForStay(Builder $query, int $stayId): Builder
    {
        return $query->where('stay_id', $stayId)->orderByDesc('recorded_at');
    }

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
