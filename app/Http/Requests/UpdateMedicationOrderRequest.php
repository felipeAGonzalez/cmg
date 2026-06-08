<?php

namespace App\Http\Requests;

/**
 * Misma validación que la creación. El controlador ignora prescribed_by_id
 * cuando el usuario es doctor (no puede cambiar el médico prescriptor).
 */
class UpdateMedicationOrderRequest extends StoreMedicationOrderRequest
{
}
