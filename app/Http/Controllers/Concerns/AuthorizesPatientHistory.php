<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Stay;

trait AuthorizesPatientHistory
{
    /**
     * Returns true when the authenticated doctor is currently assigned
     * to any active stay of the same patient as $stay.
     * Used to allow read-only access to historical stay documents.
     */
    protected function doctorCanAccessPatientHistorically(Stay $stay): bool
    {
        $user = auth()->user();

        return Stay::where('patient_id', $stay->patient_id)
            ->whereNull('discharge_date')
            ->whereHas('currentDoctors', fn($q) => $q->where('doctor_id', $user->id))
            ->exists();
    }
}
