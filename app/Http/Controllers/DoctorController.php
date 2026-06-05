<?php

namespace App\Http\Controllers;

use App\Models\Stay;
use App\Models\StayInstruction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        $doctor = auth()->user();

        $stays = Stay::active()
            ->whereHas('currentDoctors', fn($q) => $q->where('doctor_id', $doctor->id))
            ->with([
                'patient',
                'room',
                'currentDoctors' => fn($q) => $q->where('doctor_id', $doctor->id),
            ])
            ->orderBy('admission_date', 'desc')
            ->get();

        return view('doctor.my-patients', compact('stays', 'doctor'));
    }

    public function show(Stay $stay): View|RedirectResponse
    {
        $doctor = auth()->user();

        $isAssigned = $stay->currentDoctors()->where('doctor_id', $doctor->id)->exists();

        if (! $isAssigned) {
            abort(403, 'No tienes acceso a este paciente.');
        }

        $stay->load([
            'patient',
            'room',
            'currentDoctors.doctor',
            'stayDoctors.doctor',
            'roomTransfers.fromRoom',
            'roomTransfers.toRoom',
            'roomTransfers.transferredBy',
            'instructions.doctor',
            'stayDocuments.document',
        ]);

        $myAssignment = $stay->currentDoctors->where('doctor_id', $doctor->id)->first();

        return view('doctor.patient-detail', compact('stay', 'doctor', 'myAssignment'));
    }

    public function storeInstruction(Request $request, Stay $stay): RedirectResponse
    {
        $doctor = auth()->user();

        $isAssigned = $stay->currentDoctors()->where('doctor_id', $doctor->id)->exists();

        if (! $isAssigned) {
            abort(403, 'Solo puedes escribir instrucciones para pacientes asignados a ti.');
        }

        $request->validate([
            'body' => 'required|string|max:3000',
        ], [
            'body.required' => 'La instrucción no puede estar vacía.',
            'body.max'      => 'La instrucción no puede superar 3,000 caracteres.',
        ]);

        StayInstruction::create([
            'stay_id'   => $stay->id,
            'doctor_id' => $doctor->id,
            'body'      => $request->body,
        ]);

        return back()->with('success', 'Instrucción registrada correctamente.');
    }
}
