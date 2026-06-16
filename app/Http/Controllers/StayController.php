<?php

namespace App\Http\Controllers;

use App\Enums\DoctorSpecialty;
use App\Http\Requests\DischargeStayRequest;
use App\Http\Requests\StoreBirthRequest;
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

        $stay = Stay::create([
            'patient_id'     => $patient->id,
            'room_id'        => $room->id,
            'diagnosis'      => $request->diagnosis,
            'admission_date' => $request->admission_date,
        ]);

        // Genera automáticamente los documentos clínicos universales de la estancia.
        $stay->generateUniversalDocuments();

        return redirect()->route('stays.show', $room)
            ->with('success', 'Paciente ingresado correctamente al Cuarto ' . $room->number . '.');
    }

    public function show(Room $room, Request $request): View|RedirectResponse
    {
        // Todas las estancias activas del cuarto (madre + recién nacido si aplica).
        $currentStays = $room->currentStays()->with('patient')->get();

        if ($currentStays->isEmpty()) {
            return redirect()->route('rooms.index')
                ->with('warning', 'El Cuarto ' . $room->number . ' está disponible.');
        }

        // Estancia seleccionada vía ?stay=; por defecto la principal (madre).
        $selectedId = $request->query('stay');
        $stay = $currentStays->firstWhere('id', (int) $selectedId) ?? $currentStays->first();

        $stay->load([
            'patient',
            'room',
            'currentDoctors.doctor.specialties',
            'roomTransfers.fromRoom',
            'roomTransfers.toRoom',
            'roomTransfers.transferredBy',
            'instructions.doctor',
            'stayDocuments.document',
            'medicationOrders.prescribedBy',
        ]);

        $doctors    = User::where('role', 'doctor')->where('is_active', true)->orderBy('last_name_one')->get();
        $specialties = DoctorSpecialty::labels();

        // Estancias pasadas (dadas de alta) del mismo paciente, para la tab Historial.
        $previousStays = Stay::where('patient_id', $stay->patient_id)
            ->whereNotNull('discharge_date')
            ->where('id', '!=', $stay->id)
            ->orderByDesc('admission_date')
            ->with('room')
            ->get();

        return view('stays.show', compact('room', 'stay', 'currentStays', 'doctors', 'specialties', 'previousStays'));
    }

    public function createBirth(Room $room): View|RedirectResponse
    {
        if (! $room->canRegisterBirth()) {
            return redirect()->route('stays.show', $room)
                ->with('error', 'Solo se puede registrar un nacimiento cuando el cuarto tiene exactamente un paciente (la madre).');
        }

        $mother = $room->currentStays()->with('patient')->first();

        return view('stays.birth', compact('room', 'mother'));
    }

    public function storeBirth(StoreBirthRequest $request, Room $room): RedirectResponse
    {
        if (! $room->canRegisterBirth()) {
            return redirect()->route('stays.show', $room)
                ->with('error', 'No es posible registrar el nacimiento: el cuarto no tiene exactamente un paciente activo.');
        }

        $mother = $room->currentStays()->first();

        // Reutiliza expediente del bebé si ya existe y no tiene estancia activa.
        $existingPatient = Patient::searchByFullName(
            $request->name,
            $request->last_name_one,
            $request->last_name_two,
            $request->birth_date
        )->first();

        if ($existingPatient) {
            if ($existingPatient->currentStay) {
                return back()
                    ->withErrors(['name' => 'Este paciente ya tiene una estancia activa en el Cuarto ' . $existingPatient->currentStay->room->number . '.'])
                    ->withInput();
            }
            $patient = $existingPatient;
        } else {
            $patient = Patient::create($request->only(['name', 'last_name_one', 'last_name_two', 'birth_date', 'gender']));
        }

        $birthStay = Stay::create([
            'patient_id'           => $patient->id,
            'room_id'              => $room->id,
            'birth_parent_stay_id' => $mother->id,
            'diagnosis'            => $request->diagnosis,
            'admission_date'       => $request->admission_date,
        ]);

        $birthStay->generateUniversalDocuments();

        return redirect()->route('stays.show', ['room' => $room, 'stay' => $birthStay->id])
            ->with('success', 'Nacimiento registrado correctamente. El recién nacido fue ingresado al Cuarto ' . $room->number . '.');
    }

    public function discharge(DischargeStayRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return back()->with('error', 'Esta estancia ya fue dada de alta.');
        }

        $room = $stay->room;
        $stay->update([
            'discharge_date'   => now(),
            'discharge_reason' => $request->validated('discharge_reason'),
        ]);

        // Al egresar, se suspenden automáticamente las prescripciones activas.
        $stay->medicationOrders()->whereNull('suspended_at')->update([
            'suspended_at'      => now(),
            'suspended_by_id'   => auth()->id(),
            'suspension_reason' => 'Finalizada por egreso del paciente.',
        ]);

        // Igual para la orden de balance de líquidos activa (si existe).
        $stay->fluidBalanceOrders()->whereNull('suspended_at')->update([
            'suspended_at'      => now(),
            'suspended_by_id'   => auth()->id(),
            'suspension_reason' => \App\Models\FluidBalanceOrder::DISCHARGE_REASON,
        ]);

        // Igual para la orden de monitoreo de glucemia activa (si existe).
        $stay->glucoseMonitoringOrders()->whereNull('suspended_at')->update([
            'suspended_at'      => now(),
            'suspended_by_id'   => auth()->id(),
            'suspension_reason' => \App\Models\GlucoseMonitoringOrder::DISCHARGE_REASON,
        ]);

        // Las Hojas de Enfermería y la Nota de Ingreso quedan completas al egresar.
        $autoCompletedCodes = ['nursing_sheets', 'admission_note'];
        $autoCompletedDocumentIds = \App\Models\Document::whereIn('code', $autoCompletedCodes)->pluck('id');
        if ($autoCompletedDocumentIds->isNotEmpty()) {
            \App\Models\StayDocument::where('stay_id', $stay->id)
                ->whereIn('document_id', $autoCompletedDocumentIds)
                ->update([
                    'status'       => \App\Models\StayDocument::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
        }

        return redirect()->route('rooms.index')
            ->with('success', 'Paciente dado de alta. El Cuarto ' . $room->number . ' volvió a estar disponible.');
    }

    public function storeInstruction(Request $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return back()->with('error', 'No se pueden agregar indicaciones a una estancia finalizada.');
        }

        $assignedDoctorIds = $stay->currentDoctors()->pluck('doctor_id');

        if ($assignedDoctorIds->isEmpty()) {
            return back()->with('error', 'No hay médicos asignados a esta estancia. Asigna un médico primero.');
        }

        $request->validate([
            'doctor_id' => ['required', 'integer', Rule::in($assignedDoctorIds)],
            'body'      => 'required|string|max:3000',
        ], [
            'doctor_id.required' => 'Debes seleccionar el médico que dicta la indicación.',
            'doctor_id.in'       => 'El médico seleccionado no está asignado a esta estancia.',
            'body.required'      => 'La indicación no puede estar vacía.',
            'body.max'           => 'La indicación no puede superar 3,000 caracteres.',
        ]);

        StayInstruction::create([
            'stay_id'   => $stay->id,
            'doctor_id' => $request->doctor_id,
            'body'      => $request->body,
        ]);

        return back()->with('success', 'Indicación registrada correctamente.');
    }
}
