<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDischargeNoteRequest;
use App\Models\DischargeNote;
use App\Models\Document;
use App\Models\MedicalTemplate;
use App\Models\Stay;
use App\Models\StayDocument;
use Barryvdh\DomPDF\Facade\Pdf;

class DischargeNoteController extends Controller
{
    public function edit(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('stays.show', ['room' => $stay->room_id])
                ->with('error', 'No se puede editar la nota de egreso de una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);

        $note = DischargeNote::firstOrNew(['stay_id' => $stay->id]);

        $user = auth()->user();
        $templatesQuery = MedicalTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        $templates = $templatesQuery->orderBy('name')->get();

        return view('discharge-notes.edit', [
            'stay' => $stay,
            'patient' => $stay->patient,
            'note' => $note,
            'templates' => $templates,
            'sections' => config('medical_template_sections'),
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function update(UpdateDischargeNoteRequest $request, Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('stays.show', ['room' => $stay->room_id])
                ->with('error', 'No se puede editar la nota de egreso de una estancia dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        $note = DischargeNote::firstOrNew(['stay_id' => $stay->id]);

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        if (!$note->exists) {
            $data['created_by_id'] = $user->id;
        }
        $data['updated_by_id'] = $user->id;

        $note->fill($data);
        $note->save();

        $document = Document::where('code', 'discharge_note')->first();
        if ($document) {
            StayDocument::where('stay_id', $stay->id)
                ->where('document_id', $document->id)
                ->update([
                    'status' => StayDocument::STATUS_COMPLETED,
                    'completed_at' => StayDocument::where('stay_id', $stay->id)
                        ->where('document_id', $document->id)
                        ->value('completed_at') ?? now(),
                ]);
        }

        return redirect()->route('stays.show', ['room' => $stay->room_id])
            ->with('success', 'Nota de egreso guardada correctamente.');
    }

    public function pdf(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $note = DischargeNote::where('stay_id', $stay->id)->first();

        if (!$note) {
            return back()->with('error', 'No hay nota de egreso registrada para esta estancia.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);
        $note->load('attendingDoctor.specialties');

        $pdf = Pdf::loadView('pdfs.discharge-note.full', [
            'stay' => $stay,
            'patient' => $stay->patient,
            'note' => $note,
            'sections' => config('medical_template_sections'),
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'dpi' => 96,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'nota-egreso-'
            . str_replace(' ', '-', strtolower($stay->patient->fullName()))
            . '-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }

    protected function authorizeAccess(Stay $stay): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;

        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()
                ->where('doctor_id', $user->id)->exists();
            if (!$isAssigned) abort(403);
            return;
        }

        if ($user->isNurse()) {
            return;
        }

        abort(403);
    }

    protected function availableDoctorsForUser(Stay $stay)
    {
        $user = auth()->user();
        if ($user->isDoctor()) {
            return collect([$user->load('specialties')]);
        }

        return $stay->currentDoctors()
            ->with('doctor.specialties')
            ->get()
            ->pluck('doctor')
            ->unique('id');
    }
}
