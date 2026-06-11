<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStayMeasurementsRequest;
use App\Models\Stay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StayMeasurementController extends Controller
{
    public function edit(Stay $stay): View
    {
        $stay->load('room');

        return view('stays.measurements.edit', compact('stay'));
    }

    public function update(UpdateStayMeasurementsRequest $request, Stay $stay): RedirectResponse|JsonResponse
    {
        $stay->update($request->validated());

        // Soporta guardado vía AJAX (modal de talla/peso al iniciar un balance).
        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'height_cm' => $stay->height_cm,
                'weight_kg' => $stay->weight_kg,
            ]);
        }

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Medidas actualizadas correctamente.');
    }
}
