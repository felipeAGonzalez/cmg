<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicationAdministrationRequest;
use App\Http\Requests\UpdateMedicationAdministrationRequest;
use App\Models\MedicationAdministration;
use App\Models\MedicationOrder;
use App\Models\Stay;
use App\Support\Shift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MedicationAdministrationController extends Controller
{
    public function index(Stay $stay): View
    {
        $user = auth()->user();

        // Un médico solo puede consultar pacientes asignados; no ve acciones.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        $stay->load(['patient', 'room']);

        $administrations = $stay->medicationAdministrations()
            ->with(['medicationOrder', 'recordedBy'])
            ->orderByDesc('administered_at')
            ->paginate(50);

        return view('medication-administrations.index', compact('stay', 'administrations'));
    }

    public function create(Stay $stay): View|RedirectResponse
    {
        $availableOrders = $this->activeOrdersFor($stay);

        if ($availableOrders->isEmpty()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'No hay prescripciones activas para registrar administraciones.');
        }

        return view('medication-administrations.create', [
            'stay'            => $stay,
            'availableOrders' => $availableOrders,
            'statuses'        => config('administration_statuses'),
            'selectedOrderId' => (int) request('medication_order_id'),
        ]);
    }

    public function store(StoreMedicationAdministrationRequest $request, Stay $stay): RedirectResponse
    {
        $data = $request->validated();

        // La prescripción debe seguir activa al momento de guardar (evita
        // condiciones de carrera con suspensiones).
        $order = MedicationOrder::where('stay_id', $stay->id)->findOrFail($data['medication_order_id']);

        if (! $order->isActive()) {
            return back()->withInput()
                ->with('error', 'La prescripción ya no está activa; no se puede registrar la administración.');
        }

        // shift y shift_date se derivan de administered_at (no de now()).
        $shiftInfo = Shift::forDateTime($request->date('administered_at'));

        MedicationAdministration::create([
            ...$data,
            'stay_id'        => $stay->id,
            'shift'          => $shiftInfo['shift'],
            'shift_date'     => $shiftInfo['shift_date']->toDateString(),
            'recorded_by_id' => auth()->id(),
        ]);

        return redirect()->route('medicationOrders.index', $stay)
            ->with('success', 'Administración registrada correctamente.');
    }

    public function edit(MedicationAdministration $medicationAdministration): View|RedirectResponse
    {
        if (! $medicationAdministration->isEditable()) {
            abort(403, 'Solo se puede editar una administración durante el turno en que se registró.');
        }

        $medicationAdministration->load(['medicationOrder', 'stay.room', 'stay.patient']);

        return view('medication-administrations.edit', [
            'administration' => $medicationAdministration,
            'stay'           => $medicationAdministration->stay,
            'statuses'       => config('administration_statuses'),
        ]);
    }

    public function update(UpdateMedicationAdministrationRequest $request, MedicationAdministration $medicationAdministration): RedirectResponse
    {
        if (! $medicationAdministration->isEditable()) {
            abort(403, 'Solo se puede editar una administración durante el turno en que se registró.');
        }

        // No se cambian medication_order_id, administered_at ni recorded_by_id
        // (se preserva la trazabilidad original).
        $medicationAdministration->update($request->validated());

        return redirect()->route('medicationOrders.index', $medicationAdministration->stay)
            ->with('success', 'Administración actualizada.');
    }

    public function destroy(MedicationAdministration $medicationAdministration): RedirectResponse
    {
        if (! $medicationAdministration->isEditable()) {
            abort(403, 'Solo se puede eliminar una administración durante el turno en que se registró.');
        }

        $stay = $medicationAdministration->stay;
        $medicationAdministration->delete();

        return redirect()->route('medicationOrders.index', $stay)
            ->with('success', 'Administración eliminada.');
    }

    /**
     * Prescripciones actualmente activas de la estancia (estado calculado).
     *
     * @return \Illuminate\Support\Collection<int, MedicationOrder>
     */
    protected function activeOrdersFor(Stay $stay): \Illuminate\Support\Collection
    {
        return $stay->medicationOrders()
            ->whereNull('suspended_at')
            ->orderBy('medication_name')
            ->get()
            ->filter->isActive()
            ->values();
    }
}
