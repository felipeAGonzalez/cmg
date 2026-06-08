<?php

/*
|--------------------------------------------------------------------------
| Estados de una administración de medicamento
|--------------------------------------------------------------------------
|
| Catálogo del campo "estado" cuando la enfermera registra (o no) una dosis.
| Cuando el estado es distinto de 'administered' el motivo es obligatorio.
|
*/

return [
    'administered' => 'Administrada',
    'refused'      => 'Rechazada por el paciente',
    'omitted'      => 'Omitida',
];
