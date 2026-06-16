<?php

namespace App\Models;

use App\Support\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSummary extends Model
{
    protected $fillable = [
        'stay_id',
        'shift',
        'shift_date',
        'diet',
        'formula',
        'oral_liquids_ml',
        'parenteral_liquids_ml',
        'electrolytes_blood_elements',
        'urine_output_ml',
        'evacuations_count',
        'vomit_ml',
        'aspiration_ml',
        'drainage_ml',
        'drainage_type',
        'lab_biological_products',
        'reagents',
        'studies_operations',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Solo editable durante el turno en curso. */
    public function isEditable(): bool
    {
        $current = Shift::currentShift();

        return $this->shift === $current['shift']
            && $this->shift_date->equalTo($current['shift_date']);
    }

    public function shiftLabel(): string
    {
        return Shift::label($this->shift);
    }
}
