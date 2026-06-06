<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFrontSheetRequest;
use App\Models\Document;
use App\Models\Stay;
use App\Models\StayDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FrontSheetController extends Controller
{
    private const CODE = 'front_sheet';

    /**
     * Formulario para llenar / editar la Hoja Frontal de una estancia.
     */
    public function edit(Stay $stay): View|RedirectResponse
    {
        $stayDocument = $this->stayDocumentFor($stay);

        if (! $stayDocument) {
            return redirect()->route('stays.show', $stay->room)
                ->with('error', 'Esta estancia no tiene una Hoja Frontal asignada.');
        }

        $stay->load(['patient', 'room']);

        $formData = $stayDocument->form_data ?? [];

        return view('documents.front-sheet.edit', [
            'stay'            => $stay,
            'stayDocument'    => $stayDocument,
            'formData'        => $formData,
            'services'        => config('services_catalog'),
            'maritalStatuses' => config('marital_statuses'),
            'states'          => config('mexican_states'),
        ]);
    }

    /**
     * Guarda los datos capturados en form_data y marca el documento como completado.
     */
    public function update(UpdateFrontSheetRequest $request, Stay $stay): RedirectResponse
    {
        $stayDocument = $this->stayDocumentFor($stay);

        if (! $stayDocument) {
            return redirect()->route('stays.show', $stay->room)
                ->with('error', 'Esta estancia no tiene una Hoja Frontal asignada.');
        }

        $stayDocument->update([
            'form_data'    => $request->formData(),
            'status'       => StayDocument::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()->route('stays.show', ['room' => $stay->room, 'stay' => $stay->id])
            ->withFragment('documents')
            ->with('success', 'Hoja Frontal guardada correctamente.');
    }

    /**
     * Genera el PDF de la Hoja Frontal con datos en vivo de la BD.
     * Accesible para admin/nurse y para los médicos asignados a la estancia.
     */
    public function pdf(Stay $stay): Response
    {
        $this->authorizePdf($stay);

        $stayDocument = $this->stayDocumentFor($stay);

        if (! $stayDocument) {
            abort(404, 'Esta estancia no tiene una Hoja Frontal asignada.');
        }

        $stay->load(['patient', 'room', 'currentDoctors.doctor']);

        $pdf = Pdf::loadView('pdfs.documents.front-sheet', [
            'stay'     => $stay,
            'patient'  => $stay->patient,
            'formData' => $stayDocument->form_data ?? [],
        ])->setPaper('letter');

        $filename = 'hoja-frontal-' . $stay->id . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Recupera el StayDocument de la Hoja Frontal de la estancia.
     */
    private function stayDocumentFor(Stay $stay): ?StayDocument
    {
        return $stay->stayDocuments()
            ->whereHas('document', fn ($q) => $q->where('code', self::CODE))
            ->with('document')
            ->first();
    }

    /**
     * Los médicos solo pueden ver el PDF de pacientes que tienen asignados.
     * Admin, nurse y root tienen acceso completo.
     */
    private function authorizePdf(Stay $stay): void
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
}
