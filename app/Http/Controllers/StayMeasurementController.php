<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStayMeasurementsRequest;
use App\Models\Stay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StayMeasurementController extends Controller
{
    public function edit(Stay $stay): View
    {
        $stay->load('room');

        return view('stays.measurements.edit', compact('stay'));
    }

    public function update(UpdateStayMeasurementsRequest $request, Stay $stay): RedirectResponse
    {
        $stay->update($request->validated());

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Medidas actualizadas correctamente.');
    }
}
