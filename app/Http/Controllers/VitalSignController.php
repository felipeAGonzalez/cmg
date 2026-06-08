<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVitalSignRequest;
use App\Http\Requests\UpdateVitalSignRequest;
use App\Models\Stay;
use App\Models\VitalSignReading;
use App\Support\Shift;
use Illuminate\Http\RedirectResponse;

class VitalSignController extends Controller
{
    public function store(StoreVitalSignRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return redirect()->route('nursingSheets.index', $stay)
                ->with('error', 'No se pueden registrar signos vitales en una estancia finalizada.');
        }

        $shiftInfo = Shift::currentShift();

        VitalSignReading::create([
            'stay_id'     => $stay->id,
            'recorded_at' => now(),
            'shift'       => $shiftInfo['shift'],
            'shift_date'  => $shiftInfo['shift_date']->toDateString(),
            'recorded_by' => auth()->id(),
            ...$request->validated(),
        ]);

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
