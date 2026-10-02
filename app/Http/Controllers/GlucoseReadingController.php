<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGlucoseReadingRequest;
use App\Http\Requests\UpdateGlucoseReadingRequest;
use App\Models\GlucoseReading;
use App\Models\Stay;
use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class GlucoseReadingController extends Controller
{
    public function store(StoreGlucoseReadingRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return redirect()->route('nursingSheets.index', $stay)
                ->with('error', 'No se pueden registrar glucemias en una estancia finalizada.');
        }

        $activeOrder = $stay->activeGlucoseMonitoringOrder();

        if (! $activeOrder) {
            return redirect()->route('nursingSheets.index', $stay)
                ->with('error', 'No existe una indicación médica activa para monitorear la glucemia.');
        }

        $data = $request->validated();
        $recordedAt = Carbon::parse($data['recorded_at']);
        $shiftInfo = Shift::forDateTime($recordedAt);

        GlucoseReading::create([
            'stay_id' => $stay->id,
            'glucose_monitoring_order_id' => $activeOrder->id,
            'recorded_at' => $recordedAt,
            'shift' => $shiftInfo['shift'],
            'shift_date' => $shiftInfo['shift_date']->toDateString(),
            'value_mg_dl' => $data['value_mg_dl'],
            'notes' => $data['notes'] ?? null,
            'recorded_by_id' => auth()->id(),
        ]);

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Glucemia capilar registrada.');
    }

    public function update(UpdateGlucoseReadingRequest $request, GlucoseReading $glucoseReading): RedirectResponse
    {
        if (! $glucoseReading->stay->isActive() || ! $glucoseReading->isEditable()) {
            abort(403, 'No se puede editar una lectura de un turno anterior o de una estancia finalizada.');
        }

        $glucoseReading->update($request->validated());

        return redirect()->route('nursingSheets.index', $glucoseReading->stay)
            ->with('success', 'Lectura de glucemia actualizada.');
    }

    public function destroy(GlucoseReading $glucoseReading): RedirectResponse
    {
        if (! $glucoseReading->stay->isActive() || ! $glucoseReading->isEditable()) {
            abort(403, 'No se puede eliminar una lectura de un turno anterior o de una estancia finalizada.');
        }

        $stay = $glucoseReading->stay;
        $glucoseReading->delete();

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Lectura de glucemia eliminada.');
    }
}
