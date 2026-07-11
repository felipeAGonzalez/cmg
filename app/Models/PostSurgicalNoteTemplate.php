<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostSurgicalNoteTemplate extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description',
        'preop_diagnosis', 'postop_diagnosis',
        'planned_surgery', 'performed_surgery',
        'complications', 'bleeding',
        'patient_status_at_exit', 'prognosis',
        'surgical_technique',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections(): array
    {
        return [
            'preop_diagnosis'       => $this->preop_diagnosis,
            'postop_diagnosis'      => $this->postop_diagnosis,
            'planned_surgery'       => $this->planned_surgery,
            'performed_surgery'     => $this->performed_surgery,
            'complications'         => $this->complications,
            'bleeding'              => $this->bleeding,
            'patient_status_at_exit' => $this->patient_status_at_exit,
            'prognosis'             => $this->prognosis,
            'surgical_technique'    => $this->surgical_technique,
        ];
    }

    public function filledSectionsCount(): int
    {
        return collect($this->sections())
            ->filter(fn($c) => !empty(trim($c ?? '')))
            ->count();
    }
}
