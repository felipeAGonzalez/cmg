<?php

namespace App\Http\Controllers;

use App\Models\Stay;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class NursingSheetPdfController extends Controller
{
    /**
     * Genera el PDF compilado de Hojas de Enfermería con datos en vivo.
     * Accesible para admin/nurse y para los médicos asignados a la estancia.
     */
    public function show(Stay $stay): Response
    {
        $user = auth()->user();

        // Los médicos solo pueden ver el PDF de pacientes que tienen asignados.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()
                ->where('doctor_id', $user->id)->exists();

            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        // Eager loading completo para evitar N+1 al recorrer toda la estancia.
        $stay->load([
            'patient',
            'room',
            'currentDoctors.doctor',
            'vitalSignReadings.recordedBy',
            'shiftSummaries.recordedBy',
            'medicationOrders' => function ($q) {
                $q->with(['prescribedBy', 'createdBy', 'suspendedBy', 'administrations.recordedBy'])
                    ->orderBy('created_at');
            },
            'nursingEntries.recordedBy',
            'fluidBalanceOrders' => function ($q) {
                $q->with(['prescribedBy', 'createdBy', 'suspendedBy', 'days.entries.recordedBy'])
                    ->orderBy('start_date');
            },
        ]);

        // Signos vitales: lista cronológica única (sin agrupar por día calendario).
        $vitalSignReadings = $stay->vitalSignReadings->sortBy('recorded_at')->values();

        // Resúmenes de turno: solo los que existen, ordenados por fecha y turno.
        $shiftSummaries = $stay->shiftSummaries
            ->sortBy(fn ($s) => $s->shift_date->format('Y-m-d') . '_' . $s->shift)
            ->values();

        // Notas de enfermería: agrupadas por día calendario, solo días con notas.
        $nursingEntriesByDay = $stay->nursingEntries
            ->sortBy('recorded_at')
            ->groupBy(fn ($n) => $n->recorded_at->format('Y-m-d'));

        // Rango total de la estancia (para la gráfica con timeline completo).
        $admission = Carbon::parse($stay->admission_date);
        $endDate = $stay->discharge_date
            ? Carbon::parse($stay->discharge_date)
            : now();

        // ¿Hubo balance de líquidos (activo o histórico) durante la estancia?
        $hasFluidBalance = $stay->fluidBalanceOrders->isNotEmpty();

        // Gráfica de signos vitales como imagen PNG base64 (null si no hay datos o no hay GD).
        $chartImage = \App\Services\VitalSignsChartGenerator::generate(
            $vitalSignReadings,
            $admission,
            $endDate
        );

        $pdf = Pdf::loadView('pdfs.nursing-sheets.full', [
            'stay'                => $stay,
            'patient'             => $stay->patient,
            'vitalSignReadings'   => $vitalSignReadings,
            'shiftSummaries'      => $shiftSummaries,
            'nursingEntriesByDay' => $nursingEntriesByDay,
            'admissionDate'       => $admission,
            'endDate'             => $endDate,
            'hasFluidBalance'     => $hasFluidBalance,
            'chartImage'          => $chartImage,
            'generatedAt'         => now(),
            'generatedBy'         => $user,
        ])
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi'                  => 96,
                'defaultFont'          => 'sans-serif',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'hojas-enfermeria-'
            . str_replace(' ', '-', strtolower($stay->patient->fullName()))
            . '-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }
}
