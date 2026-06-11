<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFluidBalanceOrderRequest;
use App\Http\Requests\SuspendFluidBalanceOrderRequest;
use App\Models\FluidBalanceOrder;
use App\Models\Stay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FluidBalanceOrderController extends Controller
{
    public function create(Stay $stay): View|RedirectResponse
    {
        $this->authorizeCreate($stay);

        // No permitir crear si ya hay una activa.
        if ($stay->fluidBalanceOrders()->whereNull('suspended_at')->exists()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'Ya existe una orden de balance activa para este paciente.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor']);

        return view('fluid-balance-orders.create', [
            'stay'             => $stay,
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function store(StoreFluidBalanceOrderRequest $request, Stay $stay): RedirectResponse
    {
        $this->authorizeCreate($stay);

        if (! $stay->isActive()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'No se puede iniciar un balance en una estancia finalizada.');
        }

        // Re-validar por race condition: solo una activa por estancia.
        if ($stay->fluidBalanceOrders()->whereNull('suspended_at')->exists()) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'Ya existe una orden de balance activa.');
        }

        $user = auth()->user();
        $data = $request->validated();

        // El doctor solo puede prescribir a su propio nombre.
        if ($user->isDoctor()) {
            $data['prescribed_by_id'] = $user->id;
        }

        $data['created_by_id'] = $user->id;
        $data['stay_id']       = $stay->id;

        FluidBalanceOrder::create($data);

        return redirect()->route('medicationOrders.index', $stay)
            ->with('success', 'Orden de balance de líquidos iniciada.');
    }

    public function suspendForm(FluidBalanceOrder $fluidBalanceOrder): View|RedirectResponse
    {
        if (! $fluidBalanceOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if (! $fluidBalanceOrder->isActive()) {
            return redirect()->route('medicationOrders.index', $fluidBalanceOrder->stay)
                ->with('error', 'La orden ya no está activa.');
        }

        $fluidBalanceOrder->load(['stay.patient', 'stay.room', 'prescribedBy']);

        return view('fluid-balance-orders.suspend', ['order' => $fluidBalanceOrder]);
    }

    public function suspend(SuspendFluidBalanceOrderRequest $request, FluidBalanceOrder $fluidBalanceOrder): RedirectResponse
    {
        if (! $fluidBalanceOrder->canBeModifiedBy(auth()->user())) {
            abort(403);
        }

        if (! $fluidBalanceOrder->isActive()) {
            abort(403, 'La orden ya no está activa.');
        }

        $fluidBalanceOrder->update([
            'suspended_at'      => now(),
            'suspended_by_id'   => auth()->id(),
            'suspension_reason' => $request->validated()['suspension_reason'],
        ]);

        return redirect()->route('medicationOrders.index', $fluidBalanceOrder->stay)
            ->with('success', 'Orden de balance suspendida.');
    }

    /**
     * Define quién puede iniciar una orden de balance para esta estancia.
     * Mismas reglas que MedicationOrder.
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
