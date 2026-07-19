<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnesthesiaNoteTemplate extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'current_illness',
        'anesthetic_plan',
        'anesthetic_technique_and_drugs',
        'evolution_and_ucpa_discharge',
        'postop_pain_control',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections(): array
    {
        $keys = array_keys(config('anesthesia_note_template_sections', []));
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->{$key};
        }
        return $result;
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($v) => !empty(trim($v ?? '')))
            ->count();
    }
}
