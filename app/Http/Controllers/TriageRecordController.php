<?php

namespace App\Http\Controllers;

use App\Http\Requests\HospitalizeFromTriageRequest;
use App\Http\Requests\StoreTriageRecordRequest;
use App\Http\Requests\UpdateTriageRecordRequest;
use App\Models\Document;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stay;
use App\Models\StayDocument;
use App\Models\TriageRecord;
use App\Services\TriageScoreCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TriageRecordController extends Controller
{
    public function create(Patient $patient): View
    {
        $this->authorizeAccess();

        return view('triage.create', [
            'patient' => $patient,
            'now' => now(),
        ]);
    }

    public function store(StoreTriageRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = TriageScoreCalculator::calculate($data);
        $data['sum_partial_a'] = $result['sum_a'];
        $data['sum_partial_b'] = $result['sum_b'];
        $data['total_score'] = $result['total'];
        $data['color'] = $result['color'];
        $data['performed_by_id'] = auth()->id();

        $triage = TriageRecord::create($data);

        return redirect()->route('triage.show', $triage)
            ->with('success', 'Hoja de triage registrada correctamente.');
    }

    public function show(TriageRecord $triage): View
    {
        $this->authorizeAccess();

        $triage->load(['patient', 'performedBy', 'dispositionBy']);

        return view('triage.show', ['triage' => $triage]);
    }

    public function edit(TriageRecord $triage): View
    {
        $this->authorizeAccess();

        $triage->load('patient');

        return view('triage.edit', [
            'triage' => $triage,
            'patient' => $triage->patient,
        ]);
    }

    public function update(UpdateTriageRecordRequest $request, TriageRecord $triage): RedirectResponse
    {
        $data = $request->validated();

        $result = TriageScoreCalculator::calculate($data);
        $data['sum_partial_a'] = $result['sum_a'];
        $data['sum_partial_b'] = $result['sum_b'];
        $data['total_score'] = $result['total'];
        $data['color'] = $result['color'];

        $triage->update($data);

        return redirect()->route('triage.show', $triage)
            ->with('success', 'Hoja de triage actualizada.');
    }

    public function updateDisposition(Request $request, TriageRecord $triage): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'disposition' => ['required', Rule::in([
                TriageRecord::DISPOSITION_AMBULATORY,
                TriageRecord::DISPOSITION_REFUSED,
                TriageRecord::DISPOSITION_REFERRED,
            ])],
        ], [
            'disposition.required' => 'Debes seleccionar una disposición.',
            'disposition.in' => 'Para hospitalizar usa el flujo específico de hospitalización.',
        ]);

        $triage->update([
            'disposition' => $validated['disposition'],
            'disposition_at' => now(),
            'disposition_by_id' => auth()->id(),
            'evaluation_ended_at' => $triage->evaluation_ended_at ?? now(),
        ]);

        return redirect()->route('waitingRoom.index')
            ->with('success', 'Disposición registrada correctamente.');
    }

    public function hospitalize(HospitalizeFromTriageRequest $request, TriageRecord $triage): RedirectResponse
    {
        $this->authorizeAccess();

        if (! $triage->isPending()) {
            return redirect()->route('waitingRoom.index')
                ->with('error', 'Este triage ya tiene una disposición registrada.');
        }

        $data = $request->validated();
        $createdRoom = null;

        DB::transaction(function () use ($triage, $data, &$createdRoom) {
            $stay = Stay::create([
                'patient_id' => $triage->patient_id,
                'room_id' => $data['room_id'],
                'admission_date' => now(),
                'diagnosis' => $data['diagnosis'],
                'height_cm' => $data['height_cm'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
            ]);

            $stay->generateUniversalDocuments();

            $triageDoc = Document::where('code', 'triage')->first();
            if ($triageDoc) {
                StayDocument::create([
                    'stay_id' => $stay->id,
                    'document_id' => $triageDoc->id,
                    'triage_record_id' => $triage->id,
                    'status' => StayDocument::STATUS_COMPLETED,
                    'completed_at' => $triage->evaluation_ended_at ?? now(),
                ]);
            }

            $triage->update([
                'disposition' => TriageRecord::DISPOSITION_HOSPITALIZED,
                'disposition_at' => now(),
                'disposition_by_id' => auth()->id(),
                'evaluation_ended_at' => $triage->evaluation_ended_at ?? now(),
            ]);

            $createdRoom = Room::find($data['room_id']);
        });

        return redirect()->route('stays.show', $createdRoom)
            ->with('success', 'Paciente hospitalizado correctamente. Estancia creada.');
    }

    public function pdf(TriageRecord $triage)
    {
        $this->authorizeAccess();

        $triage->load(['patient', 'performedBy']);

        $pdf = Pdf::loadView('pdfs.triage.full', [
            'triage' => $triage,
            'patient' => $triage->patient,
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'dpi' => 96,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'triage-' . $triage->id . '.pdf';

        return $pdf->stream($filename);
    }

    protected function authorizeAccess(): void
    {
        if (! in_array(auth()->user()->role, ['admin', 'doctor', 'nurse'])) {
            abort(403);
        }
    }
}
