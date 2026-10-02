<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalNurseResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => (string) $this->id,
            'name' => $this->fullName(),
            'email' => (string) $this->email,
        ];
    }
}
