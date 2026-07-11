<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientHistory;
use App\Models\Stay;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AdmissionNotePdfController extends Controller
{
    use AuthorizesPatientHistory;
    /**
     * Genera el PDF de la Nota de Ingreso con datos en vivo.
     *
     * No tiene formulario propio: se alimenta de las indicaciones médicas
     * (StayInstruction) ya capturadas en la tab Médicos del paciente.
     * Accesible para admin/nurse y para los médicos asignados a la estancia.
     */
    public function show(Stay $stay): Response
    {
        $user = auth()->user();

        // Los médicos solo pueden ver el PDF de pacientes que tienen asignados.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (!$isAssigned && !$this->doctorCanAccessPatientHistorically($stay)) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        $stay->load([
            'patient',
            'room',
            'currentDoctors.doctor.specialties',
        ]);

        // Solo indicaciones del día de admisión (día 1 de la estancia).
        $instructions = $stay->instructions()
            ->with('doctor')
            ->whereDate('created_at', $stay->admission_date->toDateString())
            ->reorder('created_at', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdfs.admission-note.full', [
            'stay'         => $stay,
            'patient'      => $stay->patient,
            'instructions' => $instructions,
            'generatedAt'  => now(),
            'generatedBy'  => $user,
        ])
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi'                  => 96,
                'defaultFont'          => 'sans-serif',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'nota-ingreso-'
            . str_replace(' ', '-', strtolower($stay->patient->fullName()))
            . '-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }
}
