<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientHistory;
use App\Http\Requests\StoreTransfusionNoteRequest;
use App\Http\Requests\UpdateTransfusionNoteRequest;
use App\Models\Stay;
use App\Models\TransfusionChecklist;
use App\Models\TransfusionNote;
use App\Models\TransfusionNoteTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

class TransfusionNoteController extends Controller
{
    use AuthorizesPatientHistory;

    public function index(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $stay->load(['patient', 'room']);

        $notes = TransfusionNote::where('stay_id', $stay->id)
            ->with(['attendingDoctor', 'createdBy', 'transfusionChecklist'])
            ->orderByDesc('start_datetime')
            ->orderByDesc('created_at')
            ->get();

        return view('transfusion-notes.index', [
            'stay'      => $stay,
            'notes'     => $notes,
            'canCreate' => $stay->discharge_date === null,
        ]);
    }

    public function create(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('transfusionNotes.index', $stay)
                ->with('error', 'No se pueden crear notas transfusionales en una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);

        $user = auth()->user();
        $templatesQuery = TransfusionNoteTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        $templates = $templatesQuery->orderBy('name')->get();

        $availableChecklists = TransfusionChecklist::where('stay_id', $stay->id)
            ->whereNotNull('finalized_at')
            ->orderByDesc('finalized_at')
            ->get();

        return view('transfusion-notes.create', [
            'stay'                => $stay,
            'patient'             => $stay->patient,
            'templates'           => $templates,
            'availableChecklists' => $availableChecklists,
            'sections'            => config('transfusion_note_template_sections'),
            'availableDoctors'    => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function store(StoreTransfusionNoteRequest $request, Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'Estancia ya dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        $data['stay_id']       = $stay->id;
        $data['created_by_id'] = $user->id;

        $note = TransfusionNote::create($data);

        return redirect()->route('transfusionNotes.show', $note)
            ->with('success', 'Nota transfusional creada correctamente.');
    }

    public function show(TransfusionNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load([
            'stay.patient', 'stay.room',
            'attendingDoctor.specialties',
            'createdBy', 'updatedBy',
            'transfusionChecklist',
        ]);

        return view('transfusion-notes.show', [
            'note'     => $note,
            'stay'     => $note->stay,
            'patient'  => $note->stay->patient,
            'sections' => config('transfusion_note_template_sections'),
            'canEdit'  => $note->stay->discharge_date === null,
        ]);
    }

    public function edit(TransfusionNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('transfusionNotes.show', $note)
                ->with('error', 'No se puede editar una nota de una estancia ya dada de alta.');
        }

        $note->load(['stay.patient', 'stay.room', 'stay.currentDoctors.doctor', 'attendingDoctor', 'transfusionChecklist']);

        $user = auth()->user();
        $templatesQuery = TransfusionNoteTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        $templates = $templatesQuery->orderBy('name')->get();

        $availableChecklists = TransfusionChecklist::where('stay_id', $stay->id)
            ->whereNotNull('finalized_at')
            ->orderByDesc('finalized_at')
            ->get();

        return view('transfusion-notes.edit', [
            'note'                => $note,
            'stay'                => $stay,
            'patient'             => $note->stay->patient,
            'templates'           => $templates,
            'availableChecklists' => $availableChecklists,
            'sections'            => config('transfusion_note_template_sections'),
            'availableDoctors'    => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function update(UpdateTransfusionNoteRequest $request, TransfusionNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'Estancia ya dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();
        $data['updated_by_id'] = $user->id;

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        $note->update($data);

        return redirect()->route('transfusionNotes.show', $note)
            ->with('success', 'Nota transfusional actualizada correctamente.');
    }

    public function pdf(TransfusionNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load([
            'stay.patient', 'stay.room',
            'attendingDoctor.specialties',
            'transfusionChecklist',
        ]);

        $pdf = Pdf::loadView('pdfs.transfusion-note.full', [
            'note'        => $note,
            'stay'        => $note->stay,
            'patient'     => $note->stay->patient,
            'sections'    => config('transfusion_note_template_sections'),
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'dpi'                  => 96,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'nota-transfusional-'
            . str_replace(' ', '-', strtolower($note->stay->patient->fullName()))
            . '-' . $note->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy(TransfusionNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una nota de una estancia ya dada de alta.');
        }

        $note->delete();

        return redirect()->route('transfusionNotes.index', $stay)
            ->with('success', 'Nota transfusional eliminada.');
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

        if ($user->isNurse()) return;

        abort(403);
    }

    protected function availableDoctorsForUser(Stay $stay)
    {
        $user = auth()->user();
        if ($user->isDoctor()) return collect([$user->load('specialties')]);

        return $stay->currentDoctors()
            ->with('doctor.specialties')
            ->get()
            ->pluck('doctor')
            ->unique('id');
    }
}
