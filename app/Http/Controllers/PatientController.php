<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $query  = Patient::orderBy('last_name_one')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name_one', 'like', "%{$search}%")
                    ->orWhere('last_name_two', 'like', "%{$search}%");
            });
        }

        $patients = $query->with('currentStay')->paginate(20)->withQueryString();

        return view('patients.index', compact('patients', 'search'));
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'last_name_one' => 'required|string|max:100',
            'last_name_two' => 'nullable|string|max:100',
            'birth_date'    => 'required|date|before:today',
            'gender'        => 'required|in:M,F',
        ], [
            'name.required'          => 'El nombre del paciente es obligatorio.',
            'last_name_one.required' => 'El primer apellido es obligatorio.',
            'birth_date.required'    => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before'      => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required'        => 'El género es obligatorio.',
            'gender.in'              => 'El género debe ser Masculino o Femenino.',
        ]);

        $patient = Patient::create($data);

        if ($request->input('return_to') === 'triage') {
            return redirect()->route('triage.create', $patient)
                ->with('success', 'Paciente registrado. Continúa con la hoja de triage.');
        }

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Paciente creado correctamente.');
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'stays'                            => fn($q) => $q->orderBy('admission_date', 'desc'),
            'stays.room',
            'stays.stayDoctors.doctor',
            'stays.roomTransfers.fromRoom',
            'stays.roomTransfers.toRoom',
            'stays.roomTransfers.transferredBy',
            'triageRecords',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update($request->validated());

        if ($patient->currentStay) {
            return redirect()->route('stays.show', $patient->currentStay->room)
                ->with('success', 'Datos del paciente actualizados.');
        }

        return redirect()->route('patients.index')
            ->with('success', 'Datos del paciente actualizados.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        if ($patient->currentStay) {
            return back()
                ->with('error', 'No se puede eliminar un paciente con una estancia activa.');
        }

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }
}
