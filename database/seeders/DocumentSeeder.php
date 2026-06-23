<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Migración de nombre heredado (idempotente): solo aplica si el
        // registro antiguo aún existe y el nuevo aún no.
        if (Document::where('code', 'medical_history')->exists()
            && !Document::where('code', 'nursing_sheets')->exists()) {
            Document::where('code', 'medical_history')->update([
                'code'        => 'nursing_sheets',
                'name'        => 'Hojas de Enfermería',
                'description' => 'Registros clínicos, signos vitales y observaciones de enfermería.',
            ]);
        }

        $documents = [
            [
                'code'                   => 'triage',
                'name'                   => 'Hoja de Triage',
                'description'            => 'Clasificación de paciente para atención en el servicio de urgencia.',
                'icon'                   => 'bi-clipboard-pulse',
                'type'                   => 'triage',
                'is_universal'           => false,
                'available_on_discharge' => false,
                'display_order'          => 0,
            ],
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
                'code'                   => 'admission_note',
                'name'                   => 'Nota de Ingreso',
                'description'            => 'Estado clínico del paciente al momento de admisión.',
                'icon'                   => 'bi-box-arrow-in-right',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 2,
            ],
            [
                'code'                   => 'medical_history',
                'name'                   => 'Historia Clínica',
                'description'            => 'Historia clínica del paciente capturada por el médico tratante.',
                'icon'                   => 'bi-clipboard-heart',
                'type'                   => 'medical',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 3,
            ],
            [
                'code'                   => 'nursing_sheets',
                'name'                   => 'Hojas de Enfermería',
                'description'            => 'Registros clínicos, signos vitales y observaciones de enfermería.',
                'icon'                   => 'bi-clipboard2-pulse',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 4,
            ],
            [
                'code'                   => 'discharge_note',
                'name'                   => 'Nota de Egreso',
                'description'            => 'Resumen clínico al alta del paciente.',
                'icon'                   => 'bi-box-arrow-right',
                'type'                   => 'medical_note',
                'is_universal'           => true,
                'available_on_discharge' => true,
                'display_order'          => 5,
            ],
            [
                'code'                   => 'informed_consent',
                'name'                   => 'Consentimiento Informado',
                'description'            => 'Autorización general para recibir atención médica.',
                'icon'                   => 'bi-file-earmark-check',
                'type'                   => 'consent',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 6,
            ],
            [
                'code'                   => 'authorized_consent',
                'name'                   => 'Consentimiento Autorizado Bajo Información',
                'description'            => 'Autorización general para procedimientos diagnósticos, terapéuticos y quirúrgicos. Captura datos del responsable legal.',
                'icon'                   => 'bi-file-earmark-check',
                'type'                   => 'consent',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 7,
            ],
            [
                'code'                   => 'anesthesia_consent',
                'name'                   => 'Consentimiento Informado para Anestesia',
                'description'            => 'Consentimiento específico para la aplicación de anestesia. Incluye opciones de negación y revocación.',
                'icon'                   => 'bi-file-earmark-medical',
                'type'                   => 'consent',
                'is_universal'           => true,
                'available_on_discharge' => false,
                'display_order'          => 8,
            ],
        ];

        foreach ($documents as $document) {
            Document::updateOrCreate(['code' => $document['code']], $document);
        }
    }
}
