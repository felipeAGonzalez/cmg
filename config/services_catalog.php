<?php

/*
|--------------------------------------------------------------------------
| Catálogo de servicios hospitalarios
|--------------------------------------------------------------------------
|
| Listado de servicios que aparece en el campo "Servicio" de la Hoja Frontal.
| La clave 'other' permite capturar un servicio libre en el campo
| "service_other". No incluir 'other' aquí; se maneja por separado en el form.
|
*/

return [
    'urgencias'      => 'Urgencias',
    'medicina'       => 'Medicina Interna',
    'cirugia'        => 'Cirugía',
    'ginecologia'    => 'Ginecología y Obstetricia',
    'pediatria'      => 'Pediatría',
    'traumatologia'  => 'Traumatología y Ortopedia',
    'cardiologia'    => 'Cardiología',
    'cuidados'       => 'Cuidados Intensivos',
    'maternidad'     => 'Maternidad',
];
