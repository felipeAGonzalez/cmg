<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Migración de nombre heredado: el documento 'medical_history'
        // ("Historia Clínica") fue renombrado por el cliente a 'nursing_sheets'
        // ("Hojas de Enfermería"). Se renombra en sitio para no huérfanar los
        // stay_documents existentes que ya apuntan a ese registro.
        Document::where('code', 'medical_history')->update([
            'code'        => 'nursing_sheets',
            'name'        => 'Hojas de Enfermería',
            'description' => 'Registros clínicos, signos vitales y observaciones de enfermería.',
        ]);

        $documents = [
            [
                'code'                   => 'front_sheet',
                'name'                   => 'Hoja Frontal',
                'description'            => 'Resumen general del paciente al ingreso.',
                'icon'                   => 'bi-file-earmark-text',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 1,
            ],
            [
                'code'                   => 'nursing_sheets',
                'name'                   => 'Hojas de Enfermería',
                'description'            => 'Registros clínicos, signos vitales y observaciones de enfermería.',
                'icon'                   => 'bi-clipboard2-pulse',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 2,
            ],
            [
                'code'                   => 'admission_note',
                'name'                   => 'Nota de Ingreso',
                'description'            => 'Estado clínico del paciente al momento de admisión.',
                'icon'                   => 'bi-box-arrow-in-right',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 3,
            ],
            [
                'code'                   => 'discharge_note',
                'name'                   => 'Nota de Egreso',
                'description'            => 'Resumen clínico al alta del paciente.',
                'icon'                   => 'bi-box-arrow-right',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => true, // solo al dar de alta
                'display_order'          => 4,
            ],
            [
                'code'                   => 'informed_consent',
                'name'                   => 'Consentimiento Informado',
                'description'            => 'Autorización general para recibir atención médica.',
                'icon'                   => 'bi-file-earmark-check',
                'type'                   => 'consent',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 5,
            ],
            [
                'code'                   => 'hospitalization_consent',
                'name'                   => 'Consentimiento de Hospitalización',
                'description'            => 'Autorización para el ingreso hospitalario.',
                'icon'                   => 'bi-hospital',
                'type'                   => 'consent',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 6,
            ],
            [
                'code'                   => 'procedures_consent',
                'name'                   => 'Consentimiento de Procedimientos',
                'description'            => 'Autorización para procedimientos médicos.',
                'icon'                   => 'bi-clipboard2-check',
                'type'                   => 'consent',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 7,
            ],
        ];

        foreach ($documents as $document) {
            Document::updateOrCreate(['code' => $document['code']], $document);
        }
    }
}
