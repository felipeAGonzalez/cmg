<?php

return [
    'family_history' => [
        'label' => 'Antecedentes Hereditarios y Familiares',
        'placeholder' => 'Ej. Abuelos paternos: finados, desconoce la causa. Madre: hipertensión arterial sistémica. Padre: aparentemente sano...',
        'order' => 1,
    ],
    'non_pathological_history' => [
        'label' => 'Antecedentes Personales No Patológicos',
        'placeholder' => 'Habitación, alimentación, higiene, actividad física, herbolaria, AINEs, IVU, IVRA...',
        'order' => 2,
    ],
    'pathological_history' => [
        'label' => 'Antecedentes Personales Patológicos',
        'placeholder' => 'Enfermedades congénitas, de la infancia, crónico-degenerativos, quirúrgicos, alérgicos, toxicomanías, etilismo, tabaquismo, transfusiones, inmunizaciones, COVID19...',
        'order' => 3,
    ],
    'current_illness' => [
        'label' => 'Padecimiento Actual',
        'placeholder' => 'Narrativa del padecimiento que motiva la consulta/hospitalización.',
        'order' => 4,
    ],
    'general_symptoms' => [
        'label' => 'Síntomas Generales',
        'placeholder' => 'Ej. Fatiga, astenia, adinamia, cefalea, disnea...',
        'order' => 5,
    ],
    'physical_examination' => [
        'label' => 'Exploración Física y Somatometría',
        'placeholder' => 'Descripción narrativa de la exploración por aparatos y sistemas.',
        'order' => 6,
    ],
    'diagnostic_aids' => [
        'label' => 'Auxiliares Diagnósticos',
        'placeholder' => 'Laboratorios, USG, radiografías, tomografías, otros estudios.',
        'order' => 7,
    ],
    'main_diagnoses' => [
        'label' => 'Diagnósticos Principales',
        'placeholder' => 'Lista de diagnósticos principales (uno por línea o con viñetas).',
        'order' => 8,
    ],
    'comorbidities' => [
        'label' => 'Comórbidos',
        'placeholder' => 'Enfermedades concomitantes relevantes.',
        'order' => 9,
    ],
    'clinical_plan' => [
        'label' => 'Plan',
        'placeholder' => 'Plan terapéutico, indicaciones, medicamentos, citas de seguimiento, medidas no farmacológicas.',
        'order' => 10,
    ],
    'signature_block' => [
        'label' => 'Bloque de firma',
        'placeholder' => 'Ej. "Dr. Nombre — Especialidad — Céd. Esp. 123435". Si se deja vacío, se genera automáticamente con los datos del médico.',
        'order' => 11,
    ],
];
