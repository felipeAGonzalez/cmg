<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVitalSignRequest;
use App\Http\Requests\UpdateVitalSignRequest;
use App\Models\GlucoseReading;
use App\Models\Stay;
use App\Models\VitalSignReading;
use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class VitalSignController extends Controller
{
    public function store(StoreVitalSignRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return redirect()->route('nursingSheets.index', $stay)
                ->with('error', 'No se pueden registrar signos vitales en una estancia finalizada.');
        }

        $data        = $request->validated();
        $recordedAt  = Carbon::parse($data['recorded_at']);
        $shiftInfo   = Shift::forDateTime($recordedAt);
        $glucoseValue = $data['glucose_mg_dl'] ?? null;

        DB::transaction(function () use ($stay, $data, $recordedAt, $shiftInfo, $glucoseValue) {
            VitalSignReading::create([
                'stay_id'                  => $stay->id,
                'recorded_at'              => $recordedAt,
                'shift'                    => $shiftInfo['shift'],
                'shift_date'               => $shiftInfo['shift_date']->toDateString(),
                'heart_rate'               => $data['heart_rate'] ?? null,
                'blood_pressure_systolic'  => $data['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
                'respiratory_rate'         => $data['respiratory_rate'] ?? null,
                'temperature'              => $data['temperature'] ?? null,
                'notes'                    => $data['notes'] ?? null,
                'recorded_by'              => auth()->id(),
            ]);

            // La glucemia solo se registra si hay valor capturado Y una orden
            // de monitoreo activa para la estancia. Si no, se ignora.
            if (! empty($glucoseValue)) {
                $activeOrder = $stay->activeGlucoseMonitoringOrder();
                if ($activeOrder) {
                    GlucoseReading::create([
                        'stay_id'                     => $stay->id,
                        'glucose_monitoring_order_id' => $activeOrder->id,
                        'recorded_at'                 => $recordedAt,
                        'shift'                       => $shiftInfo['shift'],
                        'shift_date'                  => $shiftInfo['shift_date']->toDateString(),
                        'value_mg_dl'                 => $glucoseValue,
                        'recorded_by_id'              => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Signos vitales registrados.');
    }

    public function update(UpdateVitalSignRequest $request, VitalSignReading $vitalSignReading): RedirectResponse
    {
        if (! $vitalSignReading->isEditable()) {
            abort(403, 'No se puede editar un registro de un turno anterior.');
        }

        $vitalSignReading->update([
            ...$request->validated(),
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('nursingSheets.index', $vitalSignReading->stay)
            ->with('success', 'Registro actualizado.');
    }

    public function destroy(VitalSignReading $vitalSignReading): RedirectResponse
    {
        if (! $vitalSignReading->isEditable()) {
            abort(403, 'No se puede eliminar un registro de un turno anterior.');
        }

        $stay = $vitalSignReading->stay;
        $vitalSignReading->delete();

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Registro eliminado.');
    }
}
