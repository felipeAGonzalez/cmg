<?php

namespace App\Http\Requests;

/**
 * Misma validación que el registro de una toma, salvo que la hora de la toma
 * (recorded_at) es inmutable y la glucemia capilar tiene su propio ciclo de
 * vida: ninguna de las dos se puede editar al actualizar una toma. Para
 * cambiar la hora, se debe eliminar el registro y crear uno nuevo.
 */
class UpdateVitalSignRequest extends StoreVitalSignRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['recorded_at'], $rules['glucose_mg_dl']);

        return $rules;
    }
}
