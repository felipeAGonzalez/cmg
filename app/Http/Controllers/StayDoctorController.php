<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStayDoctorRequest;
use App\Models\Stay;
use App\Models\StayDoctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StayDoctorController extends Controller
{
    public function store(StoreStayDoctorRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return back()->with('error', 'No se pueden asignar médicos a una estancia finalizada.');
        }

        $doctor = User::find($request->doctor_id);

        if (! $doctor || $doctor->role !== 'doctor') {
            return back()->with('error', 'El usuario seleccionado no es un médico.');
        }

        $alreadyAssigned = $stay->currentDoctors()->where('doctor_id', $request->doctor_id)->exists();

        if ($alreadyAssigned) {
            return back()->with('error', 'Este médico ya está asignado a la estancia.');
        }

        $stay->stayDoctors()->create([
            'doctor_id'   => $request->doctor_id,
            'specialty'   => $request->specialty,
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'Médico asignado correctamente.');
    }

    public function destroy(StayDoctor $stayDoctor): RedirectResponse
    {
        if ($stayDoctor->removed_at) {
            return back()->with('error', 'Este médico ya fue removido de la estancia.');
        }

        $stayDoctor->update(['removed_at' => now()]);

        return back()->with('success', 'Médico removido de la estancia.');
    }
}
