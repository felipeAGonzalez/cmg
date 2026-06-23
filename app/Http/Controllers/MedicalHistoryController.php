<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMedicalHistoryRequest;
use App\Models\Document;
use App\Models\MedicalHistory;
use App\Models\MedicalTemplate;
use App\Models\Stay;
use App\Models\StayDocument;
use Barryvdh\DomPDF\Facade\Pdf;

class MedicalHistoryController extends Controller
{
    public function edit(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('stays.show', ['room' => $stay->room_id])
                ->with('error', 'No se puede editar la historia clínica de una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);

        $history = MedicalHistory::firstOrNew(['stay_id' => $stay->id]);

        $user = auth()->user();
        $templatesQuery = MedicalTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        $templates = $templatesQuery->orderBy('name')->get();

        return view('medical-histories.edit', [
            'stay' => $stay,
            'patient' => $stay->patient,
            'history' => $history,
            'templates' => $templates,
            'sections' => config('medical_template_sections'),
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function update(UpdateMedicalHistoryRequest $request, Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('stays.show', ['room' => $stay->room_id])
                ->with('error', 'No se puede editar la historia clínica de una estancia dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        $history = MedicalHistory::firstOrNew(['stay_id' => $stay->id]);

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        if (!$history->exists) {
            $data['created_by_id'] = $user->id;
        }
        $data['updated_by_id'] = $user->id;

        $history->fill($data);
        $history->save();

        $document = Document::where('code', 'medical_history')->first();
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
            ->with('success', 'Historia clínica guardada correctamente.');
    }

    public function pdf(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $history = MedicalHistory::where('stay_id', $stay->id)->first();

        if (!$history) {
            return back()->with('error', 'No hay historia clínica registrada para esta estancia.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);
        $history->load('attendingDoctor.specialties');

        $instructions = $stay->instructions()
            ->with('doctor')
            ->reorder('created_at', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdfs.medical-history.full', [
            'stay' => $stay,
            'patient' => $stay->patient,
            'history' => $history,
            'instructions' => $instructions,
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

        $filename = 'historia-clinica-'
            . str_replace(' ', '-', strtolower($stay->patient->fullName()))
            . '-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function templateContent(MedicalTemplate $template)
    {
        $user = auth()->user();

        $canAccess = $template->owner_id === $user->id
            || $user->isAdmin()
            || $user->isNurse();

        if (!$canAccess) {
            abort(403);
        }

        return response()->json([
            'sections' => $template->sections(),
        ]);
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
