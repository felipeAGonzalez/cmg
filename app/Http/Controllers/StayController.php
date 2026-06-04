<?php

namespace App\Http\Controllers;

use App\Enums\DoctorSpecialty;
use App\Http\Requests\StoreStayRequest;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stay;
use App\Models\StayInstruction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StayController extends Controller
{
    public function create(Room $room): View|RedirectResponse
    {
        if (! $room->isAvailable()) {
            return redirect()->route('stays.show', $room)
                ->with('warning', 'El cuarto ' . $room->number . ' ya tiene un paciente asignado.');
        }

        return view('stays.create', compact('room'));
    }

    public function store(StoreStayRequest $request, Room $room): RedirectResponse
    {
        if (! $room->isAvailable()) {
            return redirect()->route('stays.show', $room)
                ->with('error', 'El cuarto ' . $room->number . ' ya no está disponible.');
        }

        $existingPatient = Patient::searchByFullName(
            $request->name,
            $request->last_name_one,
            $request->last_name_two,
            $request->birth_date
        )->first();

        if ($existingPatient) {
            if ($existingPatient->currentStay) {
                return back()
                    ->withErrors(['name' => 'Este paciente ya tiene una estancia activa en el Cuarto ' . $existingPatient->currentStay->room->number . '. No se puede ingresar dos veces.'])
                    ->withInput();
            }
            $patient = $existingPatient;
        } else {
            $patient = Patient::create($request->only(['name', 'last_name_one', 'last_name_two', 'birth_date', 'gender']));
        }

        Stay::create([
            'patient_id'     => $patient->id,
            'room_id'        => $room->id,
            'diagnosis'      => $request->diagnosis,
            'admission_date' => $request->admission_date,
        ]);

        return redirect()->route('stays.show', $room)
            ->with('success', 'Paciente ingresado correctamente al Cuarto ' . $room->number . '.');
    }

    public function show(Room $room): View|RedirectResponse
    {
        if (! $room->currentStay) {
            return redirect()->route('rooms.index')
                ->with('warning', 'El Cuarto ' . $room->number . ' está disponible.');
        }

        $stay = $room->currentStay()->with([
            'patient',
            'currentDoctors.doctor',
            'roomTransfers.fromRoom',
            'roomTransfers.toRoom',
            'roomTransfers.transferredBy',
            'instructions.doctor',
        ])->first();

        $doctors    = User::where('role', 'doctor')->where('is_active', true)->orderBy('last_name_one')->get();
        $specialties = DoctorSpecialty::labels();

        return view('stays.show', compact('room', 'stay', 'doctors', 'specialties'));
    }

    public function discharge(Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return back()->with('error', 'Esta estancia ya fue dada de alta.');
        }

        $room = $stay->room;
        $stay->update(['discharge_date' => now()]);

        return redirect()->route('rooms.index')
            ->with('success', 'Paciente dado de alta. El Cuarto ' . $room->number . ' volvió a estar disponible.');
    }

    public function storeInstruction(Request $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return back()->with('error', 'No se pueden agregar instrucciones a una estancia finalizada.');
        }

        $assignedDoctorIds = $stay->currentDoctors()->pluck('doctor_id');

        if ($assignedDoctorIds->isEmpty()) {
            return back()->with('error', 'No hay médicos asignados a esta estancia. Asigna un médico primero.');
        }

        $request->validate([
            'doctor_id' => ['required', 'integer', Rule::in($assignedDoctorIds)],
            'body'      => 'required|string|max:3000',
        ], [
            'doctor_id.required' => 'Debes seleccionar el médico que dicta la instrucción.',
            'doctor_id.in'       => 'El médico seleccionado no está asignado a esta estancia.',
            'body.required'      => 'La instrucción no puede estar vacía.',
            'body.max'           => 'La instrucción no puede superar 3,000 caracteres.',
        ]);

        StayInstruction::create([
            'stay_id'   => $stay->id,
            'doctor_id' => $request->doctor_id,
            'body'      => $request->body,
        ]);

        return back()->with('success', 'Instrucción registrada correctamente.');
    }
}
