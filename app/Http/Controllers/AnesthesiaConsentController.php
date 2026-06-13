<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAnesthesiaConsentRequest;
use App\Models\Document;
use App\Models\Stay;
use App\Models\StayDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnesthesiaConsentController extends Controller
{
    protected const DOCUMENT_CODE = 'anesthesia_consent';

    public function edit(Stay $stay): View|RedirectResponse
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            return redirect()
                ->route('stays.show', ['room' => $stay->room, 'stay' => $stay->id])
                ->withFragment('documents')
                ->with('error', 'No se puede editar un consentimiento de una estancia ya dada de alta.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);
        $stayDocument = $this->stayDocumentFor($stay);

        $formData = array_merge($this->buildDefaults($stay), $stayDocument->form_data ?? []);

        return view('consents.anesthesia.edit', [
            'stay'             => $stay,
            'stayDocument'     => $stayDocument,
            'formData'         => $formData,
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function update(UpdateAnesthesiaConsentRequest $request, Stay $stay): RedirectResponse
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'No se puede editar un consentimiento de una estancia dada de alta.');
        }

        $stayDocument = $this->stayDocumentFor($stay);
        $user = auth()->user();
        $data = $request->validated();

        if ($user->isDoctor()) {
            $data['prescribed_by_id'] = $user->id;
        }

        // Las casillas no marcadas no se envían: normalizar a booleano explícito.
        $data['negation'] = [
            'applies' => $request->boolean('negation.applies'),
        ];
        $data['revocation'] = [
            'applies'               => $request->boolean('revocation.applies'),
            'original_consent_date' => $request->input('revocation.original_consent_date'),
            'revocation_date'       => $request->input('revocation.revocation_date'),
        ];

        $existing = $stayDocument->form_data ?? [];
        $data['created_by_id'] = $existing['created_by_id'] ?? $user->id;
        $data['updated_by_id'] = $user->id;

        $stayDocument->update([
            'form_data'    => $data,
            'status'       => StayDocument::STATUS_COMPLETED,
            'completed_at' => $stayDocument->completed_at ?? now(),
        ]);

        return redirect()
            ->route('stays.show', ['room' => $stay->room, 'stay' => $stay->id])
            ->withFragment('documents')
            ->with('success', 'Consentimiento de anestesia guardado correctamente.');
    }

    public function pdf(Stay $stay): Response|RedirectResponse
    {
        $this->authorizeAccess($stay);

        $stayDocument = $this->stayDocumentFor($stay);

        if (! $stayDocument->form_data) {
            return redirect()
                ->route('stays.show', ['room' => $stay->room, 'stay' => $stay->id])
                ->withFragment('documents')
                ->with('error', 'El consentimiento no ha sido llenado todavía.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor.specialties']);

        $pdf = Pdf::loadView('pdfs.consents.anesthesia-consent', [
            'stay'        => $stay,
            'patient'     => $stay->patient,
            'data'        => $stayDocument->form_data,
            'generatedAt' => now(),
        ])
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi'                  => 96,
                'defaultFont'          => 'sans-serif',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'consentimiento-anestesia-'
            . str_replace(' ', '-', strtolower($stay->patient->fullName()))
            . '-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }

    protected function buildDefaults(Stay $stay): array
    {
        // Pre-rellenar los datos del responsable desde el Consentimiento Autorizado,
        // si ya fue capturado. Solo aplica a campos vacíos (array_merge en edit()).
        $authorizedConsent = StayDocument::where('stay_id', $stay->id)
            ->whereHas('document', fn ($q) => $q->where('code', 'authorized_consent'))
            ->first();
        $authorizedData = $authorizedConsent?->form_data ?? [];

        $primaryDoctor = $stay->currentDoctors->first()?->doctor;

        return [
            'patient_phone'            => $authorizedData['patient_phone'] ?? '',

            'responsible_name'         => $authorizedData['responsible_name'] ?? '',
            'responsible_relationship' => $authorizedData['responsible_relationship'] ?? '',
            'responsible_phone'        => $authorizedData['responsible_phone'] ?? '',
            'responsible_address'      => $authorizedData['responsible_address'] ?? '',

            'anesthesiologist_name'    => '',
            'anesthesiologist_state'   => 'Guanajuato',

            'procedure_name'           => '',
            'anesthesia_type'          => '',
            'anesthesia_character'     => 'elective',

            'witness_1_name'           => '',
            'witness_2_name'           => '',

            'negation'                 => ['applies' => false],
            'revocation'               => [
                'applies'               => false,
                'original_consent_date' => null,
                'revocation_date'       => null,
            ],

            'prescribed_by_id'         => $primaryDoctor?->id,
        ];
    }

    protected function stayDocumentFor(Stay $stay): StayDocument
    {
        $document = Document::where('code', static::DOCUMENT_CODE)->firstOrFail();

        return $stay->stayDocuments()->firstOrCreate(
            ['document_id' => $document->id],
            ['status' => StayDocument::STATUS_PENDING],
        );
    }

    protected function authorizeAccess(Stay $stay): void
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isNurse()) {
            return;
        }

        if ($user->isDoctor()) {
            $isAssigned = $stay->currentDoctors()->where('doctor_id', $user->id)->exists();
            if (! $isAssigned) {
                abort(403, 'No tienes acceso a este paciente.');
            }

            return;
        }

        abort(403);
    }

    protected function availableDoctorsForUser(Stay $stay): Collection
    {
        $user = auth()->user();

        if ($user->isDoctor()) {
            return collect([$user]);
        }

        return $stay->currentDoctors()
            ->with('doctor')
            ->get()
            ->pluck('doctor')
            ->filter()
            ->unique('id')
            ->values();
    }
}
