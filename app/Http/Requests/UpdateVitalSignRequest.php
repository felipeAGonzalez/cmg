<?php

namespace App\Http\Requests;

/**
 * Misma validación que el registro de una toma: reglas, mensajes y la
 * comprobación de "al menos un signo vital" se heredan de StoreVitalSignRequest.
 */
class UpdateVitalSignRequest extends StoreVitalSignRequest
{
}
