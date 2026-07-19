<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnesthesiaNoteVitalReading extends Model
{
    protected $fillable = [
        'anesthesia_note_id',
        'reading_time',
        'ta_sys',
        'ta_dia',
        'fc',
        'fr',
        'temp',
        'spo2',
        'event_marker',
        'sort_order',
        'hartmann_ml',
        'glucose_ml',
        'nacl_ml',
    ];

    public function totalSerumMl(): int
    {
        return ($this->hartmann_ml ?? 0) + ($this->glucose_ml ?? 0) + ($this->nacl_ml ?? 0);
    }

    public function anesthesiaNote()
    {
        return $this->belongsTo(AnesthesiaNote::class);
    }
}
