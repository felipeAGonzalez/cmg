<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayInstruction extends Model
{
    protected $fillable = ['stay_id', 'doctor_id', 'body'];

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
