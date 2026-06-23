<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAuthorizedConsentRequest;
use App\Models\Document;
use App\Models\Stay;
use App\Models\StayDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AuthorizedConsentController extends Controller
{
    protected const DOCUMENT_CODE = 'authorized_consent';

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

        return view('consents.authorized.edit', [
            'stay'             => $stay,
            'stayDocument'     => $stayDocument,
            'formData'         => $formData,
            'availableDoctors' => $this->availableDoctorsForUser($stay),
        ]);
    }

    public function update(UpdateAuthorizedConsentRequest $request, Stay $stay): RedirectResponse
    {
        $this->authorizeAccess($stay);

        if ($stay->discharge_date !== null) {
            abort(403, 'No se puede editar un consentimiento de una estancia dada de alta.');
        }

        $stayDocument = $this->stayDocumentFor($stay);
        $user = auth()->user();
        $data = $request->validated();

        // El doctor siempre firma a su nombre.
        if ($user->isDoctor()) {
            $data['prescribed_by_id'] = $user->id;
        }

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
            ->with('success', 'Consentimiento autorizado guardado correctamente.');
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

        $pdf = Pdf::loadView('pdfs.consents.authorized-consent', [
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

        $filename = 'consentimiento-autorizado-'
            . str_replace(' ', '-', strtolower($stay->patient->fullName()))
            . '-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }

    protected function buildDefaults(Stay $stay): array
    {
        $primaryDoctor = $stay->currentDoctors->first()?->doctor;

        return [
            'doctor_name'              => $primaryDoctor?->fullName() ?? '',

            'diagnoses'                => [$stay->diagnosis ?? '', ''],
            'benefits'                 => ['', '', ''],
            'risks'                    => ['', '', ''],

            'city'                     => 'Acámbaro, Gto.',
            'signed_day'               => now()->day,
            'signed_month'             => now()->translatedFormat('F'),
            'signed_year'              => now()->year,
            'signed_time'              => now()->format('H:i'),

            'responsible_name'         => '',
            'responsible_relationship' => '',
            'responsible_phone'        => '',
            'responsible_address'      => '',

            'witness_1_name'           => '',
            'witness_2_name'           => '',

            'patient_phone'            => '',
            'prescribed_by_id'         => $primaryDoctor?->id,
        ];
    }

    /**
     * Recupera (o crea, para estancias previas a la incorporación del documento)
     * el StayDocument del consentimiento autorizado de la estancia.
     */
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
