<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientHistory;
use App\Http\Requests\StoreAnesthesiaNoteRequest;
use App\Http\Requests\UpdateAnesthesiaNoteRequest;
use App\Models\AnesthesiaNote;
use App\Models\AnesthesiaNoteTemplate;
use App\Models\PostSurgicalNote;
use App\Models\Stay;
use App\Models\User;
use App\Services\AnesthesiaVitalsChartGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AnesthesiaNoteController extends Controller
{
    use AuthorizesPatientHistory;

    public function index(Stay $stay)
    {
        $this->authorizeAccess($stay);

        $stay->load(['patient', 'room']);

        $notes = AnesthesiaNote::where('stay_id', $stay->id)
            ->with(['attendingDoctor', 'createdBy', 'postSurgicalNote'])
            ->orderByDesc('created_at')
            ->get();

        return view('anesthesia-notes.index', [
            'stay'      => $stay,
            'notes'     => $notes,
            'canCreate' => $stay->discharge_date === null,
        ]);
    }

    public function create(Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('anesthesiaNotes.index', $stay)
                ->with('error', 'No se pueden crear notas de anestesia en una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor']);

        $user = auth()->user();
        $templatesQuery = AnesthesiaNoteTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        return view('anesthesia-notes.create', [
            'stay'                      => $stay,
            'patient'                   => $stay->patient,
            'templates'                 => $templatesQuery->orderBy('name')->get(),
            'sections'                  => config('anesthesia_note_template_sections'),
            'antecedents'               => config('anesthesia_note_antecedents'),
            'aldreteScale'              => config('anesthesia_note_aldrete_scale'),
            'availableDoctors'          => $this->availableDoctorsForUser($stay),
            'doctors'                   => User::where('role', 'doctor')->orderBy('name')->get(),
            'availablePostSurgicalNotes' => PostSurgicalNote::where('stay_id', $stay->id)
                ->orderByDesc('surgery_date')->get(),
        ]);
    }

    public function store(StoreAnesthesiaNoteRequest $request, Stay $stay)
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'Estancia ya dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        $data = $this->processOrTeam($data, $request);

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        $data['stay_id']       = $stay->id;
        $data['created_by_id'] = $user->id;

        $vitalReadings = $data['vital_readings'] ?? [];
        unset($data['vital_readings']);

        DB::transaction(function () use (&$note, $data, $vitalReadings) {
            $note = AnesthesiaNote::create($data);
            $this->syncVitalReadings($note, $vitalReadings);
        });

        return redirect()->route('anesthesiaNotes.show', $note)
            ->with('success', 'Nota de Anestesia creada correctamente.');
    }

    public function show(AnesthesiaNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load([
            'stay.patient', 'stay.room',
            'attendingDoctor.specialties',
            'createdBy', 'updatedBy',
            'orSurgeonUser', 'orAssistantUser',
            'vitalReadings',
            'postSurgicalNote',
        ]);

        return view('anesthesia-notes.show', [
            'note'        => $note,
            'stay'        => $note->stay,
            'patient'     => $note->stay->patient,
            'antecedents' => config('anesthesia_note_antecedents'),
            'aldreteScale' => config('anesthesia_note_aldrete_scale'),
            'canEdit'     => $note->stay->discharge_date === null,
        ]);
    }

    public function edit(AnesthesiaNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->route('anesthesiaNotes.show', $note)
                ->with('error', 'No se puede editar una nota de una estancia ya dada de alta.');
        }

        $note->load([
            'stay.patient', 'stay.room', 'stay.currentDoctors.doctor',
            'orSurgeonUser', 'orAssistantUser', 'attendingDoctor',
            'vitalReadings', 'postSurgicalNote',
        ]);

        $user = auth()->user();
        $templatesQuery = AnesthesiaNoteTemplate::query()->with('owner');

        if ($user->isDoctor()) {
            $templatesQuery->where('owner_id', $user->id);
        } elseif ($user->isNurse()) {
            $doctorIds = $stay->currentDoctors->pluck('doctor_id');
            $templatesQuery->whereIn('owner_id', $doctorIds);
        }

        return view('anesthesia-notes.edit', [
            'note'                      => $note,
            'stay'                      => $stay,
            'patient'                   => $note->stay->patient,
            'templates'                 => $templatesQuery->orderBy('name')->get(),
            'sections'                  => config('anesthesia_note_template_sections'),
            'antecedents'               => config('anesthesia_note_antecedents'),
            'aldreteScale'              => config('anesthesia_note_aldrete_scale'),
            'availableDoctors'          => $this->availableDoctorsForUser($stay),
            'doctors'                   => User::where('role', 'doctor')->orderBy('name')->get(),
            'availablePostSurgicalNotes' => PostSurgicalNote::where('stay_id', $stay->id)
                ->orderByDesc('surgery_date')->get(),
        ]);
    }

    public function update(UpdateAnesthesiaNoteRequest $request, AnesthesiaNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'Estancia ya dada de alta.');
        }

        $user = auth()->user();
        $data = $request->validated();

        $data = $this->processOrTeam($data, $request);
        $data['updated_by_id'] = $user->id;

        if ($user->isDoctor()) {
            $data['attending_doctor_id'] = $user->id;
        }

        $vitalReadings = $data['vital_readings'] ?? [];
        unset($data['vital_readings']);

        DB::transaction(function () use ($note, $data, $vitalReadings) {
            $note->update($data);
            $this->syncVitalReadings($note, $vitalReadings);
        });

        return redirect()->route('anesthesiaNotes.show', $note)
            ->with('success', 'Nota de Anestesia actualizada correctamente.');
    }

    public function pdf(AnesthesiaNote $note)
    {
        $this->authorizeAccess($note->stay);

        $note->load([
            'stay.patient', 'stay.room',
            'attendingDoctor.specialties',
            'orSurgeonUser', 'orAssistantUser',
            'vitalReadings',
            'postSurgicalNote',
        ]);

        $chartImage = $note->vitalReadings->isNotEmpty()
            ? (new AnesthesiaVitalsChartGenerator())->generate($note->vitalReadings)
            : null;

        $pdf = Pdf::loadView('pdfs.anesthesia-note.full', [
            'note'        => $note,
            'stay'        => $note->stay,
            'patient'     => $note->stay->patient,
            'antecedents' => config('anesthesia_note_antecedents'),
            'aldreteScale' => config('anesthesia_note_aldrete_scale'),
            'chartImage'  => $chartImage,
            'generatedAt' => now(),
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'dpi'                  => 96,
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'nota-anestesia-'
            . str_replace(' ', '-', strtolower($note->stay->patient->fullName()))
            . '-' . $note->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function destroy(AnesthesiaNote $note)
    {
        $stay = $note->stay;
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una nota de una estancia ya dada de alta.');
        }

        $note->delete();

        return redirect()->route('anesthesiaNotes.index', $stay)
            ->with('success', 'Nota de Anestesia eliminada.');
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

    private function processOrTeam(array $data, $request): array
    {
        foreach (['or_surgeon', 'or_assistant'] as $role) {
            $userIdField = "{$role}_user_id";
            $otherField  = "{$role}_other_name";

            if ($request->input($userIdField) === 'other') {
                $data[$userIdField] = null;
            } else {
                $data[$otherField]  = null;
                $data[$userIdField] = $request->input($userIdField) ?: null;
            }
        }
        return $data;
    }

    private function syncVitalReadings(AnesthesiaNote $note, array $readings): void
    {
        $note->vitalReadings()->delete();

        foreach ($readings as $index => $reading) {
            if (empty($reading['reading_time'])) continue;

            $note->vitalReadings()->create([
                'reading_time'  => $reading['reading_time'],
                'ta_sys'        => $reading['ta_sys'] ?? null,
                'ta_dia'        => $reading['ta_dia'] ?? null,
                'fc'            => $reading['fc'] ?? null,
                'fr'            => $reading['fr'] ?? null,
                'temp'          => $reading['temp'] ?? null,
                'spo2'          => $reading['spo2'] ?? null,
                'event_marker'  => $reading['event_marker'] ?? null,
                'hartmann_ml'   => $reading['hartmann_ml'] ?? null,
                'glucose_ml'    => $reading['glucose_ml'] ?? null,
                'nacl_ml'       => $reading['nacl_ml'] ?? null,
                'sort_order'    => $index,
            ]);
        }
    }
}
