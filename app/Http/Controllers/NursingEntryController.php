<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNursingEntryRequest;
use App\Http\Requests\UpdateNursingEntryRequest;
use App\Models\NursingEntry;
use App\Models\Stay;
use App\Support\Shift;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NursingEntryController extends Controller
{
    public function index(Stay $stay, Request $request): View
    {
        $user = auth()->user();

        // Un médico solo puede consultar pacientes que tenga asignados.
        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()
                ->where('doctor_id', $user->id)
                ->exists();

            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }
        }

        $stay->load(['patient', 'room']);

        $categories = config('nursing_entry_categories');
        $categoryFilter = $request->query('category');

        if ($categoryFilter && ! array_key_exists($categoryFilter, $categories)) {
            $categoryFilter = null;
        }

        $query = NursingEntry::forStay($stay->id)->with('recordedBy');

        if ($categoryFilter) {
            $query->ofCategory($categoryFilter);
        }

        $entries = $query->paginate(50)->withQueryString();

        // Conteos por categoría (sin filtro), para los pills de filtro.
        $countsByCategory = NursingEntry::where('stay_id', $stay->id)
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $totalCount = $countsByCategory->sum();

        return view('nursing-entries.index', compact(
            'stay',
            'entries',
            'categories',
            'categoryFilter',
            'countsByCategory',
            'totalCount',
        ));
    }

    public function create(Stay $stay, Request $request): View
    {
        $stay->load(['patient', 'room']);

        $categories = config('nursing_entry_categories');
        $preselectedCategory = $request->query('category');

        if ($preselectedCategory && ! array_key_exists($preselectedCategory, $categories)) {
            $preselectedCategory = null;
        }

        return view('nursing-entries.create', compact(
            'stay',
            'categories',
            'preselectedCategory',
        ));
    }

    public function store(StoreNursingEntryRequest $request, Stay $stay): RedirectResponse
    {
        $data = $request->validated();

        // El turno se calcula desde recorded_at (no desde now()).
        $recordedAt = Carbon::parse($data['recorded_at']);
        $shiftInfo  = Shift::forDateTime($recordedAt);

        NursingEntry::create([
            'stay_id'        => $stay->id,
            'category'       => $data['category'],
            'description'    => $data['description'],
            'recorded_at'    => $recordedAt,
            'shift'          => $shiftInfo['shift'],
            'shift_date'     => $shiftInfo['shift_date']->toDateString(),
            'recorded_by_id' => auth()->id(),
        ]);

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(NursingEntry $nursingEntry): View|RedirectResponse
    {
        if (! $nursingEntry->isEditable()) {
            return redirect()->route('nursingSheets.index', $nursingEntry->stay)
                ->with('error', 'No se puede editar un registro de un turno anterior.');
        }

        $nursingEntry->load('stay.patient', 'stay.room');
        $categories = config('nursing_entry_categories');

        return view('nursing-entries.edit', [
            'entry'      => $nursingEntry,
            'stay'       => $nursingEntry->stay,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateNursingEntryRequest $request, NursingEntry $nursingEntry): RedirectResponse
    {
        if (! $nursingEntry->isEditable()) {
            abort(403, 'Solo se puede editar un registro durante su turno.');
        }

        // Solo se actualiza la descripción; categoría, hora y autor son inmutables.
        $nursingEntry->update([
            'description' => $request->validated()['description'],
        ]);

        return redirect()->route('nursingSheets.index', $nursingEntry->stay)
            ->with('success', 'Registro actualizado.');
    }

    public function destroy(NursingEntry $nursingEntry): RedirectResponse
    {
        if (! $nursingEntry->isEditable()) {
            abort(403, 'Solo se puede eliminar un registro durante su turno.');
        }

        $stay = $nursingEntry->stay;
        $nursingEntry->delete();

        return redirect()->route('nursingSheets.index', $stay)
            ->with('success', 'Registro eliminado.');
    }
}
