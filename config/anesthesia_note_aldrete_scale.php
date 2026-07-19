<?php

return [
    'activity' => [
        'label'   => 'Actividad',
        'options' => [
            2 => 'Mueve las 4 extremidades',
            1 => 'Mueve solo 2 extremidades',
            0 => 'No mueve ninguna extremidad',
        ],
    ],
    'respiration' => [
        'label'   => 'Respiración',
        'options' => [
            2 => 'Respira profundo, tose libremente',
            1 => 'Disnea o limitación para toser',
            0 => 'Apnea',
        ],
    ],
    'circulation' => [
        'label'   => 'Circulación',
        'options' => [
            2 => 'TA sistólica ± 20% del nivel preanestésico',
            1 => 'TA sistólica ± 20-50% del nivel preanestésico',
            0 => 'TA sistólica ± 50% del nivel preanestésico',
        ],
    ],
    'consciousness' => [
        'label'   => 'Conciencia',
        'options' => [
            2 => 'Completamente despierto',
            1 => 'Responde al ser llamado',
            0 => 'No responde',
        ],
    ],
    'saturation' => [
        'label'   => 'Saturación',
        'options' => [
            2 => 'Mantiene más del 92% de SpO2 en aire',
            1 => 'Necesita inhalar O2 para mantener SpO2 de 90%',
            0 => 'SpO2 menor de 90% aún inhalando oxígeno',
        ],
    ],
];
