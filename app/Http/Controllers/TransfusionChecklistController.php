<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientHistory;
use App\Http\Requests\StoreTransfusionChecklistRequest;
use App\Http\Requests\UpdateTransfusionChecklistRequest;
use App\Models\Stay;
use App\Models\TransfusionChecklist;
use Barryvdh\DomPDF\Facade\Pdf;

class TransfusionChecklistController extends Controller
{
    use AuthorizesPatientHistory;
    public function index(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $checklists = TransfusionChecklist::where('stay_id', $stay->id)
            ->with(['createdBy', 'updatedBy'])
            ->orderByDesc('started_at')
            ->get();

        return view('transfusion-checklists.index', [
            'stay' => $stay->load(['patient', 'room']),
            'checklists' => $checklists,
            'canCreate' => $stay->discharge_date === null,
        ]);
    }

    public function create(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('transfusionChecklists.index', $stay)
                ->with('error', 'No se pueden crear transfusiones en una estancia ya dada de alta.');
        }

        $stay->load('patient');

        return view('transfusion-checklists.create', [
            'stay' => $stay,
        ]);
    }

    public function store(StoreTransfusionChecklistRequest $request, Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403);
        }

        $checklist = TransfusionChecklist::create([
            'stay_id' => $stay->id,
            'folio' => $request->input('folio'),
            'started_at' => now(),
            'created_by_id' => auth()->id(),
        ]);

        return redirect()->route('transfusionChecklists.edit', $checklist)
            ->with('success', 'Transfusión iniciada. Captura los datos del checklist.');
    }

    public function show(TransfusionChecklist $checklist)
    {
        $this->authorizeAccess($checklist->stay);
        $checklist->load(['stay.patient', 'stay.room', 'createdBy', 'updatedBy']);

        return view('transfusion-checklists.show', [
            'checklist' => $checklist,
            'stay' => $checklist->stay,
            'patient' => $checklist->stay->patient,
        ]);
    }

    public function edit(TransfusionChecklist $checklist)
    {
        $this->authorizeAccess($checklist->stay);

        if ($checklist->stay->discharge_date !== null) {
            return redirect()->route('transfusionChecklists.index', $checklist->stay)
                ->with('error', 'No se puede editar una transfusión de una estancia dada de alta.');
        }

        $checklist->load(['stay.patient', 'stay.room']);

        return view('transfusion-checklists.edit', [
            'checklist' => $checklist,
            'stay' => $checklist->stay,
            'patient' => $checklist->stay->patient,
        ]);
    }

    public function update(UpdateTransfusionChecklistRequest $request, TransfusionChecklist $checklist)
    {
        $this->authorizeAccess($checklist->stay);

        if ($checklist->stay->discharge_date !== null) {
            abort(403);
        }

        $data = $request->validated();
        $data['updated_by_id'] = auth()->id();

        $checklist->update($data);

        $action = $request->input('action', 'save');
        if ($action === 'save_and_finalize') {
            if ($checklist->fresh()->canBeFinalized()) {
                $checklist->update(['finalized_at' => now()]);
                return redirect()->route('transfusionChecklists.show', $checklist)
                    ->with('success', 'Transfusión finalizada. Ya puedes ver el PDF.');
            } else {
                $pending = $checklist->fresh()->pendingForFinalization();
                return redirect()->back()
                    ->withInput()
                    ->with('warning', 'No se puede finalizar todavía. Faltan: ' . implode(', ', $pending));
            }
        }

        return redirect()->route('transfusionChecklists.edit', $checklist)
            ->with('success', 'Progreso guardado.');
    }

    public function finalize(TransfusionChecklist $checklist)
    {
        $this->authorizeAccess($checklist->stay);

        if ($checklist->stay->discharge_date !== null) {
            abort(403);
        }

        if (!$checklist->canBeFinalized()) {
            $pending = $checklist->pendingForFinalization();
            return redirect()->back()
                ->with('error', 'No se puede finalizar. Faltan: ' . implode(', ', $pending));
        }

        $checklist->update([
            'finalized_at' => now(),
            'updated_by_id' => auth()->id(),
        ]);

        return redirect()->route('transfusionChecklists.show', $checklist)
            ->with('success', 'Transfusión finalizada correctamente.');
    }

    public function pdf(TransfusionChecklist $checklist)
    {
        $this->authorizeAccess($checklist->stay);

        $checklist->load(['stay.patient', 'stay.room', 'createdBy']);

        $pdf = Pdf::loadView('pdfs.transfusion-checklist.full', [
            'checklist' => $checklist,
            'stay' => $checklist->stay,
            'patient' => $checklist->stay->patient,
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'landscape')
        ->setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'transfusion-'
            . str_replace(' ', '-', strtolower($checklist->stay->patient->fullName()))
            . '-' . $checklist->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy(TransfusionChecklist $checklist)
    {
        $this->authorizeAccess($checklist->stay);

        if ($checklist->isFinalized()) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una transfusión finalizada.');
        }

        $stay = $checklist->stay;
        $checklist->delete();

        return redirect()->route('transfusionChecklists.index', $stay)
            ->with('success', 'Transfusión eliminada.');
    }

    protected function authorizeAccess(Stay $stay): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;

        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (!$isAssigned && !$this->doctorCanAccessPatientHistorically($stay)) abort(403);
            return;
        }

        if ($user->isNurse()) {
            return;
        }

        abort(403);
    }
}
