<?php

namespace App\Http\Requests;

/**
 * Misma validación que el registro de una toma, salvo que la hora de la toma
 * (recorded_at) es inmutable. Para cambiar la hora, se debe eliminar el
 * registro y crear uno nuevo.
 */
class UpdateVitalSignRequest extends StoreVitalSignRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['recorded_at']);

        return $rules;
    }
}
