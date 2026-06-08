<?php

namespace App\Http\Controllers;

use App\Models\Stay;
use App\Models\ShiftSummary;
use App\Models\VitalSignReading;
use App\Support\Shift;
use Illuminate\View\View;

class NursingSheetController extends Controller
{
    public function index(Stay $stay): View
    {
        $user = auth()->user();

        // Un médico solo puede consultar pacientes que tenga asignados.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()
                ->where('doctor_id', $user->id)
                ->exists();

            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        $stay->load(['patient', 'room']);

        // Lecturas de signos vitales agrupadas por turno (clave: fecha_turno).
        $readings = VitalSignReading::forStay($stay->id)
            ->with('recordedBy')
            ->get()
            ->groupBy(fn ($r) => $r->shift_date->format('Y-m-d') . '_' . $r->shift);

        // Resúmenes de turno indexados por la misma clave.
        $summaries = ShiftSummary::where('stay_id', $stay->id)
            ->with('recordedBy')
            ->orderByDesc('shift_date')
            ->get()
            ->keyBy(fn ($s) => $s->shift_date->format('Y-m-d') . '_' . $s->shift);

        $currentShift = Shift::currentShift();
        $currentKey   = $currentShift['shift_date']->format('Y-m-d') . '_' . $currentShift['shift'];

        // Administraciones de medicamentos recientes (vista de enlace).
        $recentAdministrations = $stay->medicationAdministrations()
            ->with(['medicationOrder', 'recordedBy'])
            ->orderByDesc('administered_at')
            ->limit(10)
            ->get();
        $administrationsTotal = $stay->medicationAdministrations()->count();

        return view('nursing-sheets.index', compact(
            'stay',
            'readings',
            'summaries',
            'currentShift',
            'currentKey',
            'recentAdministrations',
            'administrationsTotal',
        ));
    }
}
