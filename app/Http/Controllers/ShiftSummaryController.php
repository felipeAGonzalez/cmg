<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShiftSummaryRequest;
use App\Models\ShiftSummary;
use App\Models\Stay;
use App\Support\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShiftSummaryController extends Controller
{
    public function edit(Stay $stay): View
    {
        $shiftInfo = Shift::currentShift();

        $summary = ShiftSummary::firstOrNew([
            'stay_id'    => $stay->id,
            'shift'      => $shiftInfo['shift'],
            'shift_date' => $shiftInfo['shift_date']->toDateString(),
        ]);

        $stay->load('room');

        return view('nursing-sheets.shift-summary.edit', compact('stay', 'summary', 'shiftInfo'));
    }

    public function update(UpdateShiftSummaryRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return redirect()->route('nursingSheets.index', $stay)
                ->with('error', 'No se puede capturar el resumen de una estancia finalizada.');
        }

        $shiftInfo = Shift::currentShift();

        $summary = ShiftSummary::firstOrNew([
            'stay_id'    => $stay->id,
            'shift'      => $shiftInfo['shift'],
            'shift_date' => $shiftInfo['shift_date']->toDateString(),
        ]);

        // Defensa server-side: si ya existe pero pertenece a otro turno, no editar.
        if ($summary->exists && ! $summary->isEditable()) {
            abort(403, 'Solo se puede editar el resumen del turno en curso.');
        }

        $data = $request->validated();

        // Si no hay drenaje, limpiar el tipo para no dejar texto huérfano.
        if (($data['drainage_ml'] ?? 0) == 0) {
            $data['drainage_type'] = null;
        }

        $summary->fill($data);
        $summary->recorded_by = auth()->id();
        $summary->save();

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Resumen del turno guardado.');
    }
}
