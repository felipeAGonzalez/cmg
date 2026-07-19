@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    {{-- ════════════════ HEADER FIJO ════════════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center g-3">

                <div class="col-auto text-center">
                    <i class="bi bi-person-fill text-primary" style="font-size:3rem; line-height:1;"></i>
                    <div class="mt-2 d-flex flex-column gap-1">
                        <span class="badge bg-secondary">Cuarto {{ $stay->room->number ?? '—' }}</span>
                        @if($stay->discharge_date !== null)
                            <span class="badge bg-secondary">Dado de alta</span>
                        @else
                            <span class="badge bg-danger">Ocupado</span>
                        @endif
                    </div>
                </div>

                <div class="col">
                    <h2 class="h4 fw-bold mb-1">{{ $stay->patient->fullName() }}</h2>
                    <div class="text-muted mb-2">
                        {{ $stay->patient->age() }} años
                        &middot; {{ $stay->patient->gender === 'M' ? 'Masculino' : 'Femenino' }}
                        &middot; {{ $stay->patient->birth_date->format('d/m/Y') }}
                    </div>
                    <div class="small">
                        <span class="fw-bold">Diagnóstico:</span>
                        <span style="white-space:pre-line;">{{ $stay->diagnosis }}</span>
                    </div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-calendar-check me-1"></i>Ingreso: {{ $stay->admission_date->format('d/m/Y H:i') }}
                        @if($stay->discharge_date !== null)
                            &nbsp;&middot;&nbsp;<i class="bi bi-box-arrow-right me-1"></i>Alta: {{ $stay->discharge_date->format('d/m/Y H:i') }}
                        @endif
                    </div>
                </div>

                <div class="col-auto d-flex gap-2 align-items-center flex-wrap">
                    <a href="{{ route('doctor.myPatients', ['tab' => $stay->discharge_date ? 'discharged' : 'active']) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        @if(auth()->user()->isDoctor()) Mis pacientes @else Pacientes @endif
                    </a>
                    @if($stay->discharge_date === null && auth()->user()->isDoctor())
                        @if(! $stay->hasDischargeIndicated())
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#indicateDischargeModal">
                                    <i class="bi bi-clock-history me-1"></i> Indicar alta
                                </button>
                                <form id="indicateAndFillNoteForm" method="POST"
                                      action="{{ route('stays.indicateDischarge', ['stay' => $stay, 'then' => 'note']) }}"
                                      style="display:none;">
                                    @csrf
                                </form>
                                <button type="button" class="btn btn-outline-warning btn-sm"
                                        onclick="document.getElementById('indicateAndFillNoteForm').submit();">
                                    <i class="bi bi-clock-history me-1"></i><i class="bi bi-pencil me-1"></i>
                                    Indicar alta y llenar Nota
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0 py-2 px-3">
                                <div class="small">
                                    <i class="bi bi-clock-history me-1"></i>
                                    <strong>Alta indicada</strong> por
                                    Dr(a). {{ $stay->dischargeIndicatedBy?->name ?? '—' }} el
                                    {{ $stay->discharge_indicated_at->format('d/m/Y H:i') }}.
                                    <span class="text-muted">Pendiente de ejecución por enfermería.</span>
                                </div>
                                <form method="POST" action="{{ route('stays.revertDischargeIndication', $stay) }}"
                                      class="ms-3 flex-shrink-0"
                                      onsubmit="return confirm('¿Revertir la indicación de alta?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise"></i> Revertir
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Banner: paciente dado de alta --}}
    @if($stay->discharge_date !== null)
        <div class="alert alert-secondary d-flex align-items-center mb-3 border-0 shadow-sm">
            <i class="bi bi-archive me-2 fs-5"></i>
            <div>
                <strong>Paciente dado de alta</strong> el
                {{ $stay->discharge_date->format('d/m/Y H:i') }}.
                @if($stay->discharge_reason)
                    Motivo: <em>{{ config('discharge_reasons.' . $stay->discharge_reason, $stay->discharge_reason) }}</em>.
                @endif
            </div>
        </div>
    @endif

    {{-- ════════════════ TABS ════════════════ --}}
    <ul class="nav nav-tabs" id="patientTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#medics" type="button" role="tab">
                <i class="bi bi-person-badge me-1"></i>Médicos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nursing" type="button" role="tab">
                <i class="bi bi-clipboard2-pulse me-1"></i>Hojas de Enfermería
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#indications" type="button" role="tab">
                <i class="bi bi-prescription2 me-1"></i>Prescripciones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                <i class="bi bi-file-earmark-text me-1"></i>Documentos
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white p-3 shadow-sm mb-3">

        {{-- ────────── TAB: Médicos (solo lectura) ────────── --}}
        <div class="tab-pane fade show active" id="medics" role="tabpanel">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-person-badge me-1"></i>Médicos asignados
            </h6>
            @if($stay->currentDoctors->isEmpty())
                <p class="text-muted fst-italic mb-0">No hay médicos asignados actualmente.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Asignado desde</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stay->currentDoctors as $sd)
                            <tr @if($sd->doctor_id === auth()->id()) class="table-primary" @endif>
                                <td>
                                    {{ $sd->doctor->fullName() }}
                                    @if($sd->doctor_id === auth()->id())
                                        <span class="badge bg-primary ms-1">Tú</span>
                                    @endif
                                </td>
                                <td>{{ $sd->doctor->specialtiesLabel() ?? '—' }}</td>
                                <td>{{ $sd->assigned_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Indicaciones médicas --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-pencil-square me-1"></i>Indicaciones médicas
                    </h6>
                    @if($doctor && $stay->discharge_date === null)
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnEscribirInstruccion">
                            <i class="bi bi-plus-circle me-1"></i>Escribir indicación
                        </button>
                    @endif
                </div>

                @if($stay->instructions->isEmpty())
                    <p class="text-muted fst-italic mb-0">No hay indicaciones registradas para esta estancia.</p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($stay->instructions as $instruction)
                        <div class="border rounded-3 p-3" style="background:#f8f9fa;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fw-semibold small">
                                    <i class="bi bi-person-circle me-1 text-primary"></i>
                                    {{ $instruction->doctor->fullName() }}
                                    @if($instruction->doctor_id === auth()->id())
                                        <span class="badge bg-primary ms-1">Tú</span>
                                    @endif
                                </span>
                                <span class="text-muted small">{{ $instruction->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mb-0" style="white-space:pre-line;">{{ $instruction->body }}</p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ────────── TAB: Hojas de Enfermería (solo lectura) ────────── --}}
        <div class="tab-pane fade" id="nursing" role="tabpanel">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Hojas de Enfermería
            </h6>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="fw-bold mb-1"><i class="bi bi-rulers me-1"></i>Talla y peso de esta estancia</div>
                    @if($stay->height_cm || $stay->weight_kg)
                        <div class="text-muted small">
                            Talla: <strong>{{ $stay->height_cm ? rtrim(rtrim(number_format($stay->height_cm, 2), '0'), '.') . ' cm' : '—' }}</strong>
                            &nbsp;·&nbsp;
                            Peso: <strong>{{ $stay->weight_kg ? rtrim(rtrim(number_format($stay->weight_kg, 2), '0'), '.') . ' kg' : '—' }}</strong>
                        </div>
                    @else
                        <div class="text-muted small">Aún no se han capturado la talla y el peso.</div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clipboard2-pulse text-primary" style="font-size:2.5rem;"></i>
                    <p class="text-muted mb-3 mt-2">
                        Consulta los signos vitales y el resumen de turno registrados por enfermería.
                    </p>
                    <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Hojas de Enfermería
                    </a>
                </div>
            </div>
        </div>

        {{-- ────────── TAB: Prescripciones ────────── --}}
        <div class="tab-pane fade" id="indications" role="tabpanel">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-prescription2 me-1"></i>Prescripciones
            </h6>

            @php
                $medOrders    = $stay->medicationOrders;
                $medActive    = $medOrders->filter->isActive();
                $medSuspended = $medOrders->filter->isSuspended();
                $medFinished  = $medOrders->filter->isFinished();
                $isAssigned   = $doctor
                    ? $stay->currentDoctors->where('doctor_id', $doctor->id)->count() > 0
                    : false;
            @endphp

            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="fw-bold fs-4 text-success">{{ $medActive->count() }}</div>
                        <div class="text-muted small">Activas</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="fw-bold fs-4 text-warning">{{ $medSuspended->count() }}</div>
                        <div class="text-muted small">Suspendidas</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="fw-bold fs-4 text-secondary">{{ $medFinished->count() }}</div>
                        <div class="text-muted small">Finalizadas</div>
                    </div>
                </div>
            </div>

            @if($medActive->isNotEmpty())
                <div class="list-group mb-3">
                    @foreach($medActive->take(3) as $preview)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold">{{ $preview->medication_name }}</span>
                            <span class="text-muted">· {{ $preview->dose }}</span>
                            <div class="text-muted small">{{ $preview->frequencyLabel() }} · {{ $preview->routeLabel() }}</div>
                        </div>
                        @if($preview->progressLabel())
                            <span class="badge bg-light text-dark border">{{ $preview->progressLabel() }}</span>
                        @else
                            <span class="badge bg-light text-dark border">Sin duración</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if($medActive->count() > 3)
                    <p class="text-muted small">… y {{ $medActive->count() - 3 }} prescripción(es) activa(s) más.</p>
                @endif
            @else
                <p class="text-muted fst-italic">Sin prescripciones activas.</p>
            @endif

            <div class="d-flex gap-2">
                <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Prescripciones del paciente
                </a>
                @if($isAssigned && $stay->isActive())
                    <a href="{{ route('medicationOrders.create', $stay) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-1"></i>Nueva prescripción
                    </a>
                @endif
            </div>
        </div>

        {{-- ────────── TAB: Documentos (solo lectura, sin acciones) ────────── --}}
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-file-earmark-text me-1"></i>Documentos clínicos
            </h6>

            @php $documents = $stay->getDocumentsOrdered(); @endphp

            @if($documents->isEmpty())
                <p class="text-muted fst-italic mb-0">Esta estancia aún no tiene documentos asignados.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:48px;"></th>
                                <th>Documento</th>
                                <th class="d-none d-md-table-cell">Tipo</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $sd)
                            @php
                                $doc             = $sd->document;
                                $isTriage            = $doc->code === 'triage';
                                $isFrontSheet        = $doc->code === 'front_sheet';
                                $isNursingSheets     = $doc->code === 'nursing_sheets';
                                $isAdmissionNote     = $doc->code === 'admission_note';
                                $isMedicalHistory    = $doc->code === 'medical_history';
                                $isDischargeNote     = $doc->code === 'discharge_note';
                                $isAuthorizedConsent = $doc->code === 'authorized_consent';
                                $isAnesthesiaConsent = $doc->code === 'anesthesia_consent';
                                $isCompleted         = $sd->status === \App\Models\StayDocument::STATUS_COMPLETED;
                                $stayActive          = $stay->discharge_date === null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                          style="width:38px; height:38px; background:#eef2f6;">
                                        <i class="bi {{ $doc->icon ?? 'bi-file-earmark' }} text-primary"></i>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $doc->name }}</div>
                                    @if($doc->description)
                                        <div class="text-muted small">{{ $doc->description }}</div>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if($doc->type === 'consent')
                                        <span class="badge bg-primary">Consentimiento</span>
                                    @else
                                        <span class="badge bg-info text-dark">Nota</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sd->statusBadgeClass() }}">{{ $sd->statusLabel() }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($isTriage && $sd->triage_record_id)
                                        <a href="{{ route('triage.pdf', $sd->triage_record_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isFrontSheet && $isCompleted)
                                        <a href="{{ route('frontSheet.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isNursingSheets)
                                        <a href="{{ route('nursingSheets.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isAdmissionNote)
                                        <a href="{{ route('admissionNote.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isMedicalHistory)
                                        @if($stayActive)
                                            <a href="{{ route('medicalHistory.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> {{ $isCompleted ? 'Editar' : 'Llenar' }}
                                            </a>
                                        @endif
                                        @if($isCompleted || \App\Models\MedicalHistory::where('stay_id', $stay->id)->exists())
                                            <a href="{{ route('medicalHistory.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        @endif
                                    @elseif($isDischargeNote)
                                        {{-- Editable por el médico tratante siempre (incluso tras alta) --}}
                                        <a href="{{ route('dischargeNote.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> {{ $isCompleted ? 'Editar' : 'Llenar' }}
                                        </a>
                                        @if($isCompleted || \App\Models\DischargeNote::where('stay_id', $stay->id)->exists())
                                            <a href="{{ route('dischargeNote.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        @endif
                                    @elseif($isAuthorizedConsent)
                                        @if($stayActive)
                                            <a href="{{ route('authorizedConsent.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </a>
                                        @endif
                                        @if($isCompleted)
                                            <a href="{{ route('authorizedConsent.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        @endif
                                    @elseif($isAnesthesiaConsent)
                                        @if($stayActive)
                                            <a href="{{ route('anesthesiaConsent.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </a>
                                        @endif
                                        @if($isCompleted)
                                            <a href="{{ route('anesthesiaConsent.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled
                                                title="{{ $isFrontSheet ? 'Aún no ha sido llenada' : 'Próximamente' }}">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Card de Notas de Evolución --}}
            @php
                $evolutionNoteCount = \App\Models\EvolutionNote::where('stay_id', $stay->id)->count();
            @endphp
            <div class="card mb-3 mt-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-up-right-circle" style="font-size:1.5rem; color:#E91E63;"></i>
                            <div>
                                <h6 class="mb-0">Notas de Evolución</h6>
                                <small class="text-muted">
                                    @if($evolutionNoteCount === 0)
                                        Sin notas registradas.
                                    @else
                                        {{ $evolutionNoteCount }} nota(s) registrada(s).
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('evolutionNotes.index', $stay) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-list-ul me-1"></i>Ver notas
                    </a>
                </div>
            </div>

            {{-- Card de transfusiones (fuera del catálogo de documentos) --}}
            @php
                $transfusionCount = \App\Models\TransfusionChecklist::where('stay_id', $stay->id)->count();
                $pendingTransfusions = \App\Models\TransfusionChecklist::where('stay_id', $stay->id)
                    ->whereNull('finalized_at')
                    ->count();
            @endphp

            <div class="card mb-3 mt-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-droplet-half" style="font-size:1.5rem; color:#E91E63;"></i>
                            <div>
                                <h6 class="mb-0">Lista de Verificación de Transfusión Segura</h6>
                                <small class="text-muted">
                                    @if($transfusionCount === 0)
                                        Sin transfusiones registradas.
                                    @else
                                        {{ $transfusionCount }}
                                        {{ $transfusionCount === 1 ? 'transfusión registrada' : 'transfusiones registradas' }}
                                        @if($pendingTransfusions > 0)
                                            · {{ $pendingTransfusions }} en progreso
                                        @endif
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('transfusionChecklists.index', $stay) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list"></i> Ver transfusiones
                        </a>
                        @if($stay->discharge_date === null)
                            <a href="{{ route('transfusionChecklists.create', $stay) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Nueva
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card de Notas Transfusionales --}}
            @php
                $transfusionNoteCount = \App\Models\TransfusionNote::where('stay_id', $stay->id)->count();
            @endphp
            <div class="card mb-3 mt-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-droplet-half" style="font-size:1.5rem; color:#E91E63;"></i>
                            <div>
                                <h6 class="mb-0">Notas Transfusionales</h6>
                                <small class="text-muted">
                                    @if($transfusionNoteCount === 0)
                                        Sin notas registradas.
                                    @else
                                        {{ $transfusionNoteCount }}
                                        {{ $transfusionNoteCount === 1 ? 'nota registrada' : 'notas registradas' }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('transfusionNotes.index', $stay) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list"></i> Ver notas
                        </a>
                        @if($stay->discharge_date === null)
                            <a href="{{ route('transfusionNotes.create', $stay) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Nueva
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card de Notas Postquirúrgicas --}}
            @php
                $postSurgicalNoteCount = \App\Models\PostSurgicalNote::where('stay_id', $stay->id)->count();
            @endphp
            <div class="card mb-3 mt-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-scissors" style="font-size:1.5rem; color:#E91E63;"></i>
                            <div>
                                <h6 class="mb-0">Notas Postquirúrgicas</h6>
                                <small class="text-muted">
                                    @if($postSurgicalNoteCount === 0)
                                        Sin notas registradas.
                                    @else
                                        {{ $postSurgicalNoteCount }}
                                        {{ $postSurgicalNoteCount === 1 ? 'nota registrada' : 'notas registradas' }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('postSurgicalNotes.index', $stay) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list"></i> Ver notas
                        </a>
                        @if($stay->discharge_date === null)
                            <a href="{{ route('postSurgicalNotes.create', $stay) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Nueva
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card de Notas de Anestesia --}}
    @php
        $anesthesiaNoteCount = \App\Models\AnesthesiaNote::where('stay_id', $stay->id)->count();
    @endphp
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-lungs" style="font-size:1.5rem; color:#E91E63;"></i>
                <strong>Notas de Anestesia</strong>
                <small class="text-muted d-block">
                    @if($anesthesiaNoteCount === 0)
                        Sin notas registradas.
                    @else
                        {{ $anesthesiaNoteCount }}
                        {{ $anesthesiaNoteCount === 1 ? 'nota registrada' : 'notas registradas' }}
                    @endif
                </small>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('anesthesiaNotes.index', $stay) }}"
                   class="btn btn-sm btn-outline-primary">Ver notas</a>
                @if($stay->discharge_date === null)
                    <a href="{{ route('anesthesiaNotes.create', $stay) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Nueva
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════ ACCIONES INFERIORES ════════════════ --}}
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('doctor.myPatients', ['tab' => $stay->discharge_date ? 'discharged' : 'active']) }}"
           class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-left me-1"></i>
            @if(auth()->user()->isDoctor()) Volver a mis pacientes @else Volver a pacientes @endif
        </a>
    </div>

    {{-- Modal: Escribir instrucción (doctor en su propio nombre) --}}
    <div class="modal fade" id="instruccionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('doctor.storeInstruction', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>Escribir indicación médica
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-1">
                            <span class="text-muted small">
                                Paciente: <strong>{{ $stay->patient->fullName() }}</strong>
                                &nbsp;·&nbsp; Cuarto {{ $stay->room->number }}
                            </span>
                        </div>
                        <div class="mt-3">
                            <label for="instruccion_body" class="form-label fw-semibold">Indicación</label>
                            <textarea id="instruccion_body" name="body"
                                      class="form-control @error('body') is-invalid @enderror"
                                      rows="6" maxlength="3000"
                                      placeholder="Escribe aquí la indicación médica..."
                                      required>{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-end"><span id="charCount">0</span> / 3000 caracteres</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>Guardar indicación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Modal: Indicar alta --}}
    @if(! $stay->hasDischargeIndicated() && $stay->discharge_date === null)
    <div class="modal fade" id="indicateDischargeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('stays.indicateDischarge', $stay) }}">
                @csrf
                <div class="modal-content shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-clock-history me-1"></i>Indicar alta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            ¿Confirmas que el paciente
                            <strong>{{ $stay->patient->fullName() }}</strong>
                            está listo para alta?
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Esta acción <strong>no</strong> da de alta al paciente.
                            Solo registra tu indicación clínica. La enfermera ejecutará el alta
                            cuando pueda y capturará el motivo correspondiente.
                        </p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-1"></i>Confirmar indicación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>{{-- /container --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Anclas de tabs en la URL ──────────────────────────────
    const validHashes = ['#medics', '#nursing', '#indications', '#documents'];
    const initialHash = window.location.hash;
    if (validHashes.includes(initialHash)) {
        const trigger = document.querySelector('[data-bs-target="' + initialHash + '"]');
        if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
    document.querySelectorAll('#patientTabs button[data-bs-toggle="tab"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) history.replaceState(null, '', target);
        });
    });

    // ── Modal de instrucción ──────────────────────────────────
    const btnEscribir = document.getElementById('btnEscribirInstruccion');
    const modalEl     = document.getElementById('instruccionModal');
    if (btnEscribir && modalEl) {
        btnEscribir.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    }

    const textarea  = document.getElementById('instruccion_body');
    const charCount = document.getElementById('charCount');
    if (textarea && charCount) {
        const update = () => charCount.textContent = textarea.value.length;
        textarea.addEventListener('input', update);
        update();
    }

    @if($errors->has('body'))
    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif

});
</script>
@endpush
