<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalizedPatientResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        return [
            'patient_id' => (string) $this->patient_id,
            'hospitalization_id' => (string) $this->id,
            'patient_name' => $this->patient->fullName(),
            'room_id' => (string) $this->room_id,
            'room_number' => (string) $this->room->number,
        ];
    }
}
