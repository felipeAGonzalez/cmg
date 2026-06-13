<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGlucoseMonitoringOrderRequest;
use App\Http\Requests\SuspendGlucoseMonitoringOrderRequest;
use App\Models\GlucoseMonitoringOrder;
use App\Models\Stay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GlucoseMonitoringOrderController extends Controller
{
    public function create(Stay $stay): View|RedirectResponse
    {
        $this->authorizeCreate($stay);

        // No permitir crear si ya hay una activa.
        if ($stay->glucoseMonitoringOrders()->whereNull('suspended_at')->exists()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'Ya existe una orden de monitoreo de glucemia activa para este paciente.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor']);

        return view('glucose-monitoring-orders.create', [
            'stay'             => $stay,
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function store(StoreGlucoseMonitoringOrderRequest $request, Stay $stay): RedirectResponse
    {
        $this->authorizeCreate($stay);

        if (! $stay->isActive()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'No se puede iniciar un monitoreo en una estancia finalizada.');
        }

        // Re-validar por race condition: solo una activa por estancia.
        if ($stay->glucoseMonitoringOrders()->whereNull('suspended_at')->exists()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'Ya existe una orden de monitoreo de glucemia activa.');
        }

        $user = auth()->user();
        $data = $request->validated();

        // El doctor solo puede prescribir a su propio nombre.
        if ($user->isDoctor()) {
            $data['prescribed_by_id'] = $user->id;
        }

        $data['created_by_id'] = $user->id;
        $data['stay_id']       = $stay->id;

        GlucoseMonitoringOrder::create($data);

        return redirect()->route('medicationOrders.index', $stay)
            ->with('success', 'Monitoreo de glucemia capilar iniciado.');
    }

    public function suspendForm(GlucoseMonitoringOrder $glucoseMonitoringOrder): View|RedirectResponse
    {
        if (! $glucoseMonitoringOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if (! $glucoseMonitoringOrder->isActive()) {
            return redirect()->route('medicationOrders.index', $glucoseMonitoringOrder->stay)
                ->with('error', 'La orden ya no está activa.');
        }

        $glucoseMonitoringOrder->load(['stay.patient', 'stay.room', 'prescribedBy']);

        return view('glucose-monitoring-orders.suspend', ['order' => $glucoseMonitoringOrder]);
    }

    public function suspend(SuspendGlucoseMonitoringOrderRequest $request, GlucoseMonitoringOrder $glucoseMonitoringOrder): RedirectResponse
    {
        if (! $glucoseMonitoringOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if (! $glucoseMonitoringOrder->isActive()) {
            abort(403, 'La orden ya no está activa.');
        }

        $glucoseMonitoringOrder->update([
            'suspended_at'      => now(),
            'suspended_by_id'   => auth()->id(),
            'suspension_reason' => $request->validated()['suspension_reason'],
        ]);

        return redirect()->route('medicationOrders.index', $glucoseMonitoringOrder->stay)
            ->with('success', 'Monitoreo de glucemia suspendido.');
    }

    /**
     * Define quién puede iniciar una orden de monitoreo para esta estancia.
     * Mismas reglas que MedicationOrder / FluidBalanceOrder.
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
