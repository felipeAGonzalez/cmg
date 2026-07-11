<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransfusionNoteTemplate extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description',
        'diagnoses_and_indication', 'compatibility_verification',
        'evolution_narrative', 'conclusion',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections(): array
    {
        return [
            'diagnoses_and_indication'  => $this->diagnoses_and_indication,
            'compatibility_verification' => $this->compatibility_verification,
            'evolution_narrative'        => $this->evolution_narrative,
            'conclusion'                 => $this->conclusion,
        ];
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($content) => !empty(trim($content ?? '')))
            ->count();
    }
}
