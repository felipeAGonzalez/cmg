<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFluidBalanceEntryRequest;
use App\Http\Requests\UpdateFluidBalanceEntryRequest;
use App\Models\FluidBalanceDay;
use App\Models\FluidBalanceEntry;
use App\Models\FluidBalanceOrder;
use App\Services\InsensibleLossesCalculator;
use App\Services\ShiftHoursCalculator;
use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FluidBalanceCaptureController extends Controller
{
    public function index(FluidBalanceOrder $fluidBalanceOrder): View|RedirectResponse
    {
        $user = auth()->user();
        $stay = $fluidBalanceOrder->stay;

        // Un médico solo puede consultar pacientes que tenga asignados.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        // Bloqueo defensivo: talla y peso son necesarios para el cálculo.
        if (! $stay->height_cm || ! $stay->weight_kg) {
            return redirect()->route('medicationOrders.index', $stay)
                ->with('error', 'Falta capturar talla y peso del paciente.');
        }

        // Auto-cerrar días vencidos (lazy).
        foreach ($fluidBalanceOrder->days as $day) {
            $day->autoCloseIfExpired();
        }

        $fluidBalanceOrder->load([
            'stay.patient', 'stay.room', 'prescribedBy',
            'days.entries.recordedBy',
        ]);

        return view('fluid-balance-captures.index', [
            'order' => $fluidBalanceOrder,
            'stay'  => $stay,
        ]);
    }

    public function store(StoreFluidBalanceEntryRequest $request, FluidBalanceOrder $fluidBalanceOrder): RedirectResponse
    {
        if (! $fluidBalanceOrder->isActive()) {
            return back()->with('error', 'La orden de balance no está activa.');
        }

        $stay = $fluidBalanceOrder->stay;
        if (! $stay->height_cm || ! $stay->weight_kg) {
            return back()->with('error', 'Falta capturar talla y peso del paciente.');
        }

        $data       = $request->validated();
        $recordedAt = Carbon::parse($data['recorded_at']);

        // 1. Determinar/crear el día de balance al que pertenece la toma.
        $day = $this->resolveDayForEntry($fluidBalanceOrder, $recordedAt);

        // 2. Auto-cerrar los demás días vencidos.
        foreach ($fluidBalanceOrder->days()->whereNull('closed_at')->get() as $d) {
            if ($d->id !== $day->id) {
                $d->autoCloseIfExpired();
            }
        }

        // 3. No permitir agregar a un día cerrado.
        if ($day->isClosed()) {
            return back()->with('error', 'No se puede agregar a un día ya cerrado.');
        }

        // 4. Calcular pérdidas insensibles (snapshot al momento de la toma).
        $patient     = $stay->patient;
        $age         = InsensibleLossesCalculator::ageInYears(
            Carbon::parse($patient->birth_date),
            $recordedAt
        );
        $temperature = InsensibleLossesCalculator::lastTemperatureBefore($stay, $recordedAt);
        $weight      = (float) $stay->weight_kg;

        $formula = InsensibleLossesCalculator::selectFormula($age, $weight, $temperature);
        $hours   = ShiftHoursCalculator::hoursForNewEntry($fluidBalanceOrder, $recordedAt);
        $insensibleLosses = InsensibleLossesCalculator::calculate($formula, $weight, $temperature, $hours);

        // 5. Crear la entry.
        $shiftInfo = Shift::forDateTime($recordedAt);

        FluidBalanceEntry::create([
            'fluid_balance_day_id' => $day->id,
            'recorded_at'          => $recordedAt,
            'shift'                => $shiftInfo['shift'],
            'shift_date'           => $shiftInfo['shift_date']->toDateString(),
            'oral_ml'              => $data['oral_ml'] ?? 0,
            'iv_solution_ml'       => $data['iv_solution_ml'] ?? 0,
            'blood_ml'             => $data['blood_ml'] ?? 0,
            'plasma_ml'            => $data['plasma_ml'] ?? 0,
            'sonda_ml'             => $data['sonda_ml'] ?? 0,
            'other_inputs_ml'      => $data['other_inputs_ml'] ?? 0,
            'urine_ml'             => $data['urine_ml'] ?? 0,
            'evacuation_ml'        => $data['evacuation_ml'] ?? 0,
            'vomit_ml'             => $data['vomit_ml'] ?? 0,
            'hemorrhage_ml'        => $data['hemorrhage_ml'] ?? 0,
            'suction_ml'           => $data['suction_ml'] ?? 0,
            'canalization_ml'      => $data['canalization_ml'] ?? 0,
            'insensible_losses_ml' => $insensibleLosses,
            'formula_used'         => $formula,
            'temperature_at_entry' => $temperature,
            'weight_at_entry'      => $weight,
            'hours_since_previous' => $hours,
            'observation'          => $data['observation'] ?? null,
            'recorded_by_id'       => auth()->id(),
        ]);

        $day->recalculate();

        return redirect()->route('fluidBalanceCaptures.index', $fluidBalanceOrder)
            ->with('success', 'Toma de balance registrada.');
    }

    public function update(UpdateFluidBalanceEntryRequest $request, FluidBalanceEntry $fluidBalanceEntry): RedirectResponse
    {
        if (! $fluidBalanceEntry->isEditable()) {
            abort(403, 'Solo se puede editar una toma durante su turno.');
        }

        $data = $request->validated();

        // Solo se editan valores numéricos y observación. recorded_at, shift y
        // los snapshots de la fórmula NO se recalculan (preservan auditoría).
        $fluidBalanceEntry->update([
            'oral_ml'         => $data['oral_ml'] ?? 0,
            'iv_solution_ml'  => $data['iv_solution_ml'] ?? 0,
            'blood_ml'        => $data['blood_ml'] ?? 0,
            'plasma_ml'       => $data['plasma_ml'] ?? 0,
            'sonda_ml'        => $data['sonda_ml'] ?? 0,
            'other_inputs_ml' => $data['other_inputs_ml'] ?? 0,
            'urine_ml'        => $data['urine_ml'] ?? 0,
            'evacuation_ml'   => $data['evacuation_ml'] ?? 0,
            'vomit_ml'        => $data['vomit_ml'] ?? 0,
            'hemorrhage_ml'   => $data['hemorrhage_ml'] ?? 0,
            'suction_ml'      => $data['suction_ml'] ?? 0,
            'canalization_ml' => $data['canalization_ml'] ?? 0,
            'observation'     => $data['observation'] ?? null,
        ]);

        $fluidBalanceEntry->day->recalculate();

        return redirect()->route('fluidBalanceCaptures.index', $fluidBalanceEntry->day->fluidBalanceOrder)
            ->with('success', 'Toma actualizada.');
    }

    public function destroy(FluidBalanceEntry $fluidBalanceEntry): RedirectResponse
    {
        if (! $fluidBalanceEntry->isEditable()) {
            abort(403, 'Solo se puede eliminar una toma durante su turno.');
        }

        $day   = $fluidBalanceEntry->day;
        $order = $day->fluidBalanceOrder;

        $fluidBalanceEntry->delete();
        $day->recalculate();

        return redirect()->route('fluidBalanceCaptures.index', $order)
            ->with('success', 'Toma eliminada.');
    }

    /**
     * Resuelve a qué día pertenece la nueva entry; si no existe, lo crea.
     */
    protected function resolveDayForEntry(FluidBalanceOrder $order, Carbon $recordedAt): FluidBalanceDay
    {
        // Día existente cuyo rango [start_at, end_at) contenga recordedAt.
        $existingDay = $order->days()
            ->where('start_at', '<=', $recordedAt)
            ->where('end_at', '>', $recordedAt)
            ->first();

        if ($existingDay) {
            return $existingDay;
        }

        $lastDay = $order->days()->orderByDesc('day_number')->first();

        if (! $lastDay) {
            // Primer día: start_at = más reciente entre admission_date y order start_date.
            $admission  = $order->stay->admission_date
                ? Carbon::parse($order->stay->admission_date)
                : null;
            $orderStart = Carbon::parse($order->start_date)->startOfDay();
            $startAt    = $admission && $admission->greaterThan($orderStart)
                ? $admission
                : $orderStart;
            $dayNumber  = 1;
        } else {
            // Día siguiente: empieza donde terminó el anterior.
            $startAt   = $lastDay->end_at;
            $dayNumber = $lastDay->day_number + 1;
        }

        return FluidBalanceDay::create([
            'fluid_balance_order_id' => $order->id,
            'day_number'             => $dayNumber,
            'start_at'               => $startAt,
            'end_at'                 => $startAt->copy()->addHours(24),
        ]);
    }
}
