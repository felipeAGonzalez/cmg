<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicationOrderRequest;
use App\Http\Requests\SuspendMedicationOrderRequest;
use App\Http\Requests\UpdateMedicationOrderRequest;
use App\Models\MedicationOrder;
use App\Models\Stay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MedicationOrderController extends Controller
{
    public function index(Stay $stay): View
    {
        $user = auth()->user();

        // Un médico solo puede consultar pacientes que tenga asignados.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor']);

        $orders = MedicationOrder::forStay($stay->id)
            ->with(['prescribedBy', 'createdBy', 'updatedBy', 'suspendedBy', 'administrations.recordedBy'])
            ->orderByDesc('created_at')
            ->get();

        // Agrupar por estado calculado en vivo.
        $grouped = $orders->groupBy(fn ($o) => $o->status());

        // Órdenes de balance de líquidos (tab "Otras indicaciones").
        $activeFluidBalanceOrder = $stay->fluidBalanceOrders()
            ->whereNull('suspended_at')
            ->with(['prescribedBy', 'createdBy'])
            ->first();

        $pastFluidBalanceOrders = $stay->fluidBalanceOrders()
            ->whereNotNull('suspended_at')
            ->with(['prescribedBy', 'suspendedBy'])
            ->orderByDesc('suspended_at')
            ->get();

        // Órdenes de monitoreo de glucemia (tab "Otras prescripciones").
        $activeGlucoseOrder = $stay->glucoseMonitoringOrders()
            ->whereNull('suspended_at')
            ->with(['prescribedBy', 'createdBy'])
            ->first();

        $pastGlucoseOrders = $stay->glucoseMonitoringOrders()
            ->whereNotNull('suspended_at')
            ->with(['prescribedBy', 'suspendedBy'])
            ->orderByDesc('suspended_at')
            ->get();

        return view('medication-orders.index', [
            'stay'                    => $stay,
            'orders'                  => $orders,
            'activeOrders'            => $grouped[MedicationOrder::STATUS_ACTIVE] ?? collect(),
            'suspendedOrders'         => $grouped[MedicationOrder::STATUS_SUSPENDED] ?? collect(),
            'finishedOrders'          => $grouped[MedicationOrder::STATUS_FINISHED] ?? collect(),
            'activeFluidBalanceOrder' => $activeFluidBalanceOrder,
            'pastFluidBalanceOrders'  => $pastFluidBalanceOrders,
            'activeGlucoseOrder'      => $activeGlucoseOrder,
            'pastGlucoseOrders'       => $pastGlucoseOrders,
        ]);
    }

    public function create(Stay $stay): View
    {
        $this->authorizeCreate($stay);

        $stay->load('currentDoctors.doctor');

        return view('medication-orders.create', [
            'stay'             => $stay,
            'availableDoctors' => $this->availableDoctorsForUser($stay),
            'routes'           => config('medication_routes'),
            'frequencies'      => config('medication_frequencies'),
        ]);
    }

    public function store(StoreMedicationOrderRequest $request, Stay $stay): RedirectResponse
    {
        $this->authorizeCreate($stay);

        if (! $stay->isActive()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'No se pueden registrar prescripciones en una estancia finalizada.');
        }

        $user = auth()->user();
        $data = $request->validated();

        // El doctor solo puede prescribir a su propio nombre.
        if ($user->isDoctor()) {
            $data['prescribed_by_id'] = $user->id;
        }

        $data['created_by_id'] = $user->id;

        MedicationOrder::create([...$data, 'stay_id' => $stay->id]);

        return redirect()->route('medicationOrders.index', $stay)
            ->with('success', 'Prescripción registrada correctamente.');
    }

    public function edit(MedicationOrder $medicationOrder): View|RedirectResponse
    {
        if (! $medicationOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if ($medicationOrder->isSuspended()) {
            return redirect()->route('medicationOrders.index', $medicationOrder->stay)
                ->with('error', 'No se puede editar una prescripción suspendida.');
        }

        $medicationOrder->load('stay.currentDoctors.doctor');

        return view('medication-orders.edit', [
            'order'            => $medicationOrder,
            'stay'             => $medicationOrder->stay,
            'availableDoctors' => $this->availableDoctorsForUser($medicationOrder->stay),
            'routes'           => config('medication_routes'),
            'frequencies'      => config('medication_frequencies'),
        ]);
    }

    public function update(UpdateMedicationOrderRequest $request, MedicationOrder $medicationOrder): RedirectResponse
    {
        if (! $medicationOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if ($medicationOrder->isSuspended()) {
            abort(403, 'No se puede editar una prescripción suspendida.');
        }

        $user = auth()->user();
        $data = $request->validated();

        // El doctor no puede cambiar el médico prescriptor.
        if ($user->isDoctor()) {
            unset($data['prescribed_by_id']);
        }

        $data['updated_by_id'] = $user->id;

        $medicationOrder->update($data);

        return redirect()->route('medicationOrders.index', $medicationOrder->stay)
            ->with('success', 'Prescripción actualizada.');
    }

    public function suspendForm(MedicationOrder $medicationOrder): View|RedirectResponse
    {
        if (! $medicationOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if ($medicationOrder->isSuspended()) {
            return redirect()->route('medicationOrders.index', $medicationOrder->stay)
                ->with('error', 'La prescripción ya está suspendida.');
        }

        $medicationOrder->load('prescribedBy');

        return view('medication-orders.suspend', ['order' => $medicationOrder]);
    }

    public function suspend(SuspendMedicationOrderRequest $request, MedicationOrder $medicationOrder): RedirectResponse
    {
        if (! $medicationOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if ($medicationOrder->isSuspended()) {
            abort(403, 'La prescripción ya está suspendida.');
        }

        $medicationOrder->update([
            'suspended_at'      => now(),
            'suspended_by_id'   => auth()->id(),
            'suspension_reason' => $request->validated()['suspension_reason'],
        ]);

        return redirect()->route('medicationOrders.index', $medicationOrder->stay)
            ->with('success', 'Prescripción suspendida.');
    }

    /**
     * Define quién puede crear una nueva prescripción para esta estancia.
     * - Doctor: solo si está asignado a la estancia.
     * - Nurse: solo si hay al menos un doctor asignado (para prescribir a su nombre).
     * - Admin/root: siempre.
     */
    protected function authorizeCreate(Stay $stay): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403);
            }
            return;
        }

        if ($user->isNurse()) {
            if (! $stay->currentDoctors()->exists()) {
                abort(403, 'No hay médicos asignados al paciente para prescribir a su nombre.');
            }
            return;
        }

        abort(403);
    }

    /**
     * Doctores que el usuario actual puede seleccionar como prescriptor.
     * - Doctor: solo a sí mismo.
     * - Nurse/admin/root: doctores actualmente asignados al stay.
     *
     * @return Collection<int, \App\Models\User>
     */
    protected function availableDoctorsForUser(Stay $stay): Collection
    {
        $user = auth()->user();

        if ($user->isDoctor()) {
            return new Collection([$user]);
        }

        return $stay->currentDoctors()
            ->with('doctor')
            ->get()
            ->pluck('doctor')
            ->unique('id')
            ->values();
    }
}
