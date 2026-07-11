<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientHistory;
use App\Http\Requests\StoreEvolutionNoteRequest;
use App\Http\Requests\UpdateEvolutionNoteRequest;
use App\Models\EvolutionNote;
use App\Models\EvolutionTemplate;
use App\Models\MedicationAdministration;
use App\Models\Stay;
use App\Models\VitalSignReading;
use Barryvdh\DomPDF\Facade\Pdf;

class EvolutionNoteController extends Controller
{
    use AuthorizesPatientHistory;
    public function index(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $stay->load(['patient', 'room']);

        $notes = EvolutionNote::where('stay_id', $stay->id)
            ->with(['attendingDoctor', 'createdBy'])
            ->orderByDesc('note_datetime')
            ->get();

        return view('evolution-notes.index', [
            'stay'      => $stay,
            'notes'     => $notes,
            'canCreate' => $stay->discharge_date === null,
        ]);
    }

    public function create(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('stays.show', $stay)
                ->with('error', 'No se pueden crear notas de evolución en una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);

        $user = auth()->user();
        $templatesQuery = EvolutionTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        $templates = $templatesQuery->orderBy('name')->get();

        $latestVitals = VitalSignReading::where('stay_id', $stay->id)
            ->orderByDesc('recorded_at')
            ->first();

        return view('evolution-notes.create', [
            'stay'             => $stay,
            'patient'          => $stay->patient,
            'templates'        => $templates,
            'latestVitals'     => $latestVitals,
            'sections'         => config('evolution_template_sections'),
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function store(StoreEvolutionNoteRequest $request, Stay $stay)
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

        $data['stay_id']        = $stay->id;
        $data['created_by_id']  = $user->id;

        $note = EvolutionNote::create($data);

        return redirect()->route('evolutionNotes.show', $note)
            ->with('success', 'Nota de evolución creada correctamente.');
    }

    public function show(EvolutionNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load(['stay.patient', 'stay.room', 'attendingDoctor.specialties', 'createdBy', 'updatedBy']);

        $latestVitals = VitalSignReading::where('stay_id', $note->stay_id)
            ->orderByDesc('recorded_at')
            ->first();

        $medications = $this->getMedicationsInRange($note);

        return view('evolution-notes.show', [
            'note'         => $note,
            'stay'         => $note->stay,
            'patient'      => $note->stay->patient,
            'latestVitals' => $latestVitals,
            'medications'  => $medications,
            'sections'     => config('evolution_template_sections'),
        ]);
    }

    public function edit(EvolutionNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('evolutionNotes.show', $note)
                ->with('error', 'No se puede editar una nota de una estancia ya dada de alta.');
        }

        $note->load(['stay.patient', 'stay.room', 'stay.currentDoctors.doctor', 'attendingDoctor']);

        $user = auth()->user();
        $templatesQuery = EvolutionTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        $templates = $templatesQuery->orderBy('name')->get();

        $latestVitals = VitalSignReading::where('stay_id', $note->stay_id)
            ->orderByDesc('recorded_at')
            ->first();

        return view('evolution-notes.edit', [
            'note'             => $note,
            'stay'             => $stay,
            'patient'          => $note->stay->patient,
            'templates'        => $templates,
            'latestVitals'     => $latestVitals,
            'sections'         => config('evolution_template_sections'),
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function update(UpdateEvolutionNoteRequest $request, EvolutionNote $note)
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

        return redirect()->route('evolutionNotes.show', $note)
            ->with('success', 'Nota de evolución actualizada correctamente.');
    }

    public function pdf(EvolutionNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load(['stay.patient', 'stay.room', 'attendingDoctor.specialties']);

        $latestVitals = VitalSignReading::where('stay_id', $note->stay_id)
            ->orderByDesc('recorded_at')
            ->first();

        $medications = $this->getMedicationsInRange($note);

        $pdf = Pdf::loadView('pdfs.evolution-note.full', [
            'note'        => $note,
            'stay'        => $note->stay,
            'patient'     => $note->stay->patient,
            'latestVitals' => $latestVitals,
            'medications' => $medications,
            'sections'    => config('evolution_template_sections'),
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'dpi'                  => 96,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'nota-evolucion-'
            . str_replace(' ', '-', strtolower($note->stay->patient->fullName()))
            . '-' . $note->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy(EvolutionNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una nota de una estancia ya dada de alta.');
        }

        $note->delete();

        return redirect()->route('evolutionNotes.index', $stay)
            ->with('success', 'Nota de evolución eliminada.');
    }

    protected function getMedicationsInRange(EvolutionNote $note)
    {
        if (!$note->medications_from || !$note->medications_to) {
            return collect();
        }

        return MedicationAdministration::where('stay_id', $note->stay_id)
            ->whereBetween('administered_at', [$note->medications_from, $note->medications_to])
            ->with(['medicationOrder', 'recordedBy'])
            ->orderBy('administered_at')
            ->get();
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
