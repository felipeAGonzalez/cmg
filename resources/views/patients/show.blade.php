@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:880px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-person me-2"></i>Expediente — {{ $patient->fullName() }}
        </h4>
    </div>

    {{-- Datos del paciente --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-person me-1"></i>Datos del paciente
                </h6>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
                @endif
            </div>
            <div class="row g-2">
                <div class="col-sm-6"><span class="text-muted small">Nombre completo</span>
                    <div class="fw-semibold">{{ $patient->fullName() }}</div></div>
                <div class="col-sm-3"><span class="text-muted small">Edad</span>
                    <div class="fw-semibold">{{ $patient->age() }} años</div></div>
                <div class="col-sm-3"><span class="text-muted small">Género</span>
                    <div class="fw-semibold">{{ $patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</div></div>
                <div class="col-sm-6"><span class="text-muted small">Fecha de nacimiento</span>
                    <div class="fw-semibold">{{ $patient->birth_date->format('d/m/Y') }}</div></div>
            </div>
        </div>
    </div>

    {{-- Historial de triages --}}
    @if($patient->triageRecords->isNotEmpty())
    <h5 class="fw-bold mb-3">
        <i class="bi bi-clipboard-pulse me-2"></i>Triages
        <span class="badge bg-secondary ms-1">{{ $patient->triageRecords->count() }}</span>
    </h5>
    @foreach($patient->triageRecords as $tr)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <span class="badge {{ $tr->colorBadgeClass() }} me-2">{{ $tr->colorLabel() }}</span>
                <strong>{{ $tr->decisionLabel() }}</strong>
                <span class="text-muted small ms-2">
                    {{ $tr->evaluation_started_at->format('d/m/Y H:i') }}
                    &middot; Puntaje: {{ $tr->total_score }}
                    @if($tr->folio) &middot; Folio: {{ $tr->folio }} @endif
                </span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('triage.show', $tr) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye"></i> Ver
                </a>
                <a href="{{ route('triage.pdf', $tr) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Historial de estancias --}}
    <h5 class="fw-bold mb-3">
        <i class="bi bi-clock-history me-2"></i>Historial de estancias
        <span class="badge bg-secondary ms-1">{{ $patient->stays->count() }}</span>
    </h5>

    @forelse($patient->stays as $stay)
    @php
        // Count available documents for this stay
        $docCount = 0;
        $docCount++; // Nota de Ingreso: always available
        if ($stay->medicalHistory) $docCount++;
        $docCount += $stay->evolutionNotes->count();
        if ($stay->dischargeNote) $docCount++;
        $docCount += $stay->transfusionChecklists->count();
        // StayDocuments that have a known PDF route
        $knownCodes = ['front_sheet', 'authorized_consent', 'anesthesia_consent'];
        $catalogDocs = $stay->stayDocuments->filter(fn($sd) => in_array($sd->document->code ?? '', $knownCodes));
        $docCount += $catalogDocs->count();
    @endphp
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-semibold">
                    <i class="bi bi-door-closed me-1"></i>Cuarto {{ $stay->room->number ?? '—' }}
                    — {{ $stay->admission_date->format('d/m/Y') }}
                </span>
                @if($stay->isActive())
                    <span class="badge bg-danger ms-2">Activa</span>
                @else
                    <span class="badge bg-secondary ms-2">
                        Alta: {{ $stay->discharge_date->format('d/m/Y') }}
                    </span>
                @endif
            </div>
            <button class="btn btn-sm btn-outline-primary"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#stayDocs{{ $stay->id }}"
                    aria-expanded="false">
                <i class="bi bi-folder2-open me-1"></i>
                Documentos ({{ $docCount }})
            </button>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <span class="text-muted small">Diagnóstico</span>
                <div style="white-space:pre-line;">{{ $stay->diagnosis }}</div>
            </div>

            {{-- Médicos de esta estancia --}}
            @if($stay->stayDoctors->isNotEmpty())
            <div class="mb-3">
                <span class="text-muted small">Médicos que atendieron</span>
                <ul class="mb-0 mt-1">
                    @foreach($stay->stayDoctors as $sd)
                    <li>
                        {{ $sd->doctor->fullName() }}
                        — {{ \App\Enums\DoctorSpecialty::from($sd->specialty)->label() }}
                        @if($sd->removed_at)
                            <span class="text-muted small">(hasta {{ $sd->removed_at->format('d/m/Y') }})</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Traslados de esta estancia --}}
            @if($stay->roomTransfers->isNotEmpty())
            <div class="mb-3">
                <span class="text-muted small">Traslados</span>
                <div class="table-responsive mt-1">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>De</th><th>A</th><th>Fecha</th><th>Por</th></tr>
                        </thead>
                        <tbody>
                            @foreach($stay->roomTransfers as $transfer)
                            <tr>
                                <td>Cuarto {{ $transfer->fromRoom->number }}</td>
                                <td>Cuarto {{ $transfer->toRoom->number }}</td>
                                <td>{{ $transfer->transferred_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $transfer->transferredBy->fullName() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Accordion de documentos --}}
            <div class="collapse" id="stayDocs{{ $stay->id }}">
                <div class="border-top pt-3 mt-1">
                    <p class="text-muted small mb-2">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        <strong>Documentos de esta estancia</strong>
                    </p>
                    <ul class="list-group list-group-flush">

                        {{-- Nota de Ingreso (siempre disponible) --}}
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-clipboard-check text-primary me-1"></i>
                                <strong>Nota de Ingreso</strong>
                                <small class="text-muted d-block ms-3">
                                    {{ $stay->admission_date->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <a href="{{ route('admissionNote.pdf', $stay) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </li>

                        {{-- Historia Clínica --}}
                        @if($stay->medicalHistory)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-clipboard-pulse text-info me-1"></i>
                                <strong>Historia Clínica</strong>
                                <small class="text-muted d-block ms-3">
                                    Actualizada: {{ $stay->medicalHistory->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <a href="{{ route('medicalHistory.pdf', $stay) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </li>
                        @endif

                        {{-- Notas de Evolución --}}
                        @foreach($stay->evolutionNotes as $evolution)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-arrow-up-right-circle text-success me-1"></i>
                                <strong>Nota de Evolución</strong>
                                <small class="text-muted d-block ms-3">
                                    {{ $evolution->note_datetime->format('d/m/Y H:i') }}
                                    @if($evolution->attendingDoctor)
                                        &middot; Dr(a). {{ $evolution->attendingDoctor->name }}
                                    @endif
                                </small>
                            </div>
                            <a href="{{ route('evolutionNotes.pdf', $evolution) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </li>
                        @endforeach

                        {{-- Nota de Alta --}}
                        @if($stay->dischargeNote)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-box-arrow-right text-warning me-1"></i>
                                <strong>Nota de Alta</strong>
                                @if($stay->dischargeNote->isComplete())
                                    <span class="badge bg-success ms-1">Completa</span>
                                @else
                                    <span class="badge bg-warning text-dark ms-1">Incompleta</span>
                                @endif
                                <small class="text-muted d-block ms-3">
                                    Actualizada: {{ $stay->dischargeNote->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <a href="{{ route('dischargeNote.pdf', $stay) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </li>
                        @endif

                        {{-- Listas de Verificación de Transfusión (solo finalizadas) --}}
                        @foreach($stay->transfusionChecklists as $transfusion)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-droplet-half text-danger me-1"></i>
                                <strong>Lista de Verificación de Transfusión</strong>
                                @if($transfusion->folio)
                                    <span class="text-muted small ms-1">· Folio: {{ $transfusion->folio }}</span>
                                @endif
                                <small class="text-muted d-block ms-3">
                                    Finalizada: {{ $transfusion->finalized_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <a href="{{ route('transfusionChecklists.pdf', $transfusion) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </li>
                        @endforeach

                        {{-- Documentos del catálogo completados (Hoja Frontal, Consentimientos) --}}
                        @foreach($catalogDocs as $sd)
                        @php
                            $pdfRoute = match($sd->document->code) {
                                'front_sheet'          => route('frontSheet.pdf', $stay),
                                'authorized_consent'   => route('authorizedConsent.pdf', $stay),
                                'anesthesia_consent'   => route('anesthesiaConsent.pdf', $stay),
                                default                => null,
                            };
                        @endphp
                        @if($pdfRoute)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-file-earmark-check text-secondary me-1"></i>
                                <strong>{{ $sd->document->name }}</strong>
                                <small class="text-muted d-block ms-3">
                                    @if($sd->completed_at)
                                        Completado: {{ $sd->completed_at->format('d/m/Y H:i') }}
                                    @endif
                                </small>
                            </div>
                            <a href="{{ $pdfRoute }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </li>
                        @endif
                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>
    @empty
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-1"></i>Este paciente no tiene estancias registradas.
        </div>
    @endforelse
</div>
@endsection
