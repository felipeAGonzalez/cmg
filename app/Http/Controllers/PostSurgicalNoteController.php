<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientHistory;
use App\Http\Requests\StorePostSurgicalNoteRequest;
use App\Http\Requests\UpdatePostSurgicalNoteRequest;
use App\Models\PostSurgicalNote;
use App\Models\PostSurgicalNoteTemplate;
use App\Models\Stay;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class PostSurgicalNoteController extends Controller
{
    use AuthorizesPatientHistory;

    public function index(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $stay->load(['patient', 'room']);

        $notes = PostSurgicalNote::where('stay_id', $stay->id)
            ->with(['attendingDoctor', 'createdBy', 'surgeonUser', 'assistantUser', 'anesthesiologistUser'])
            ->orderByDesc('surgery_date')
            ->orderByDesc('created_at')
            ->get();

        return view('post-surgical-notes.index', [
            'stay'      => $stay,
            'notes'     => $notes,
            'canCreate' => $stay->discharge_date === null,
        ]);
    }

    public function create(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('postSurgicalNotes.index', $stay)
                ->with('error', 'No se pueden crear notas postquirúrgicas en una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor']);

        $user = auth()->user();
        $templatesQuery = PostSurgicalNoteTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        return view('post-surgical-notes.create', [
            'stay'             => $stay,
            'patient'          => $stay->patient,
            'templates'        => $templatesQuery->orderBy('name')->get(),
            'sections'         => config('post_surgical_note_template_sections'),
            'availableDoctors' => $this->availableDoctorsForUser($stay),
            'doctors'          => User::where('role', 'doctor')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePostSurgicalNoteRequest $request, Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'Estancia ya dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        $data = $this->processSurgicalTeam($data, $request);

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        $data['stay_id']       = $stay->id;
        $data['created_by_id'] = $user->id;

        $note = PostSurgicalNote::create($data);

        return redirect()->route('postSurgicalNotes.show', $note)
            ->with('success', 'Nota Postquirúrgica creada correctamente.');
    }

    public function show(PostSurgicalNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load([
            'stay.patient', 'stay.room',
            'attendingDoctor.specialties',
            'createdBy', 'updatedBy',
            'surgeonUser', 'assistantUser', 'anesthesiologistUser',
        ]);

        return view('post-surgical-notes.show', [
            'note'     => $note,
            'stay'     => $note->stay,
            'patient'  => $note->stay->patient,
            'sections' => config('post_surgical_note_template_sections'),
            'canEdit'  => $note->stay->discharge_date === null,
        ]);
    }

    public function edit(PostSurgicalNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('postSurgicalNotes.show', $note)
                ->with('error', 'No se puede editar una nota de una estancia ya dada de alta.');
        }

        $note->load(['stay.patient', 'stay.room', 'stay.currentDoctors.doctor',
            'surgeonUser', 'assistantUser', 'anesthesiologistUser', 'attendingDoctor']);

        $user = auth()->user();
        $templatesQuery = PostSurgicalNoteTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        return view('post-surgical-notes.edit', [
            'note'             => $note,
            'stay'             => $stay,
            'patient'          => $note->stay->patient,
            'templates'        => $templatesQuery->orderBy('name')->get(),
            'sections'         => config('post_surgical_note_template_sections'),
            'availableDoctors' => $this->availableDoctorsForUser($stay),
            'doctors'          => User::where('role', 'doctor')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePostSurgicalNoteRequest $request, PostSurgicalNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'Estancia ya dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        $data = $this->processSurgicalTeam($data, $request);
        $data['updated_by_id'] = $user->id;

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        $note->update($data);

        return redirect()->route('postSurgicalNotes.show', $note)
            ->with('success', 'Nota Postquirúrgica actualizada correctamente.');
    }

    public function pdf(PostSurgicalNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load([
            'stay.patient', 'stay.room',
            'attendingDoctor.specialties',
            'surgeonUser', 'assistantUser', 'anesthesiologistUser',
        ]);

        $pdf = Pdf::loadView('pdfs.post-surgical-note.full', [
            'note'        => $note,
            'stay'        => $note->stay,
            'patient'     => $note->stay->patient,
            'sections'    => config('post_surgical_note_template_sections'),
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'dpi'                  => 96,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'nota-postquirurgica-'
            . str_replace(' ', '-', strtolower($note->stay->patient->fullName()))
            . '-' . $note->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy(PostSurgicalNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una nota de una estancia ya dada de alta.');
        }

        $note->delete();

        return redirect()->route('postSurgicalNotes.index', $stay)
            ->with('success', 'Nota Postquirúrgica eliminada.');
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
        if ($user->isDoctor()) return collect([$user]);

        return $stay->currentDoctors()
            ->with('doctor')
            ->get()
            ->pluck('doctor')
            ->unique('id');
    }

    private function processSurgicalTeam(array $data, $request): array
    {
        foreach (['surgeon', 'assistant', 'anesthesiologist'] as $role) {
            $userIdField  = "{$role}_user_id";
            $otherField   = "{$role}_other_name";

            if ($request->input($userIdField) === 'other') {
                $data[$userIdField] = null;
                // $data[$otherField] already present from validated data
            } else {
                $data[$otherField]  = null;
                $data[$userIdField] = $request->input($userIdField) ?: null;
            }
        }

        return $data;
    }
}
