<?php

namespace App\Http\Requests;

/**
 * Mismas reglas que Store, pero sin recorded_at: la hora de la toma es
 * inmutable una vez registrada (preserva el snapshot de la fórmula).
 */
class UpdateFluidBalanceEntryRequest extends StoreFluidBalanceEntryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['recorded_at']);

        return $rules;
    }
}
