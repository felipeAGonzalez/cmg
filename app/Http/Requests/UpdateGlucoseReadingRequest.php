<?php

namespace App\Http\Requests;

class UpdateGlucoseReadingRequest extends StoreGlucoseReadingRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['recorded_at']);

        return $rules;
    }
}
