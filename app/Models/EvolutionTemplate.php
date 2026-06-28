<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvolutionTemplate extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description',
        'antecedents', 'subjective', 'objective', 'analysis',
        'diagnosis', 'prognosis', 'plan',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections(): array
    {
        return [
            'antecedents' => $this->antecedents,
            'subjective'  => $this->subjective,
            'objective'   => $this->objective,
            'analysis'    => $this->analysis,
            'diagnosis'   => $this->diagnosis,
            'prognosis'   => $this->prognosis,
            'plan'        => $this->plan,
        ];
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($content) => !empty(trim($content ?? '')))
            ->count();
    }
}
