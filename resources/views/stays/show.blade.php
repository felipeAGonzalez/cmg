@extends('layouts.app')

@section('content')
@php $user = Auth::user(); @endphp
<div class="container py-4" style="max-width:900px;">

    {{-- Errores de validación (formularios de médico / instrucción redirigen aquí) --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ════════════════ HEADER FIJO ════════════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center g-3">

                {{-- Izquierda: ícono + badges --}}
                <div class="col-auto text-center">
                    <i class="bi bi-person-fill text-primary" style="font-size:3rem; line-height:1;"></i>
                    <div class="mt-2 d-flex flex-column gap-1">
                        <span class="badge bg-secondary">Cuarto {{ $room->number }}</span>
                        <span class="badge bg-danger">Ocupado</span>
                    </div>
                </div>

                {{-- Centro: datos del paciente --}}
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
                    </div>
                </div>

                {{-- Derecha: volver --}}
                <div class="col-auto">
                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Volver al tablero
                    </a>
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

    {{-- ════════════════ SELECTOR DE PACIENTE (madre + recién nacido) ════════════════ --}}
    @if($currentStays->count() > 1)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="text-muted small me-1">
                    <i class="bi bi-people-fill me-1"></i>Pacientes en este cuarto:
                </span>
                @foreach($currentStays as $cs)
                <a href="{{ route('stays.show', ['room' => $room, 'stay' => $cs->id]) }}"
                   class="btn btn-sm {{ $cs->id === $stay->id ? 'btn-primary' : 'btn-outline-primary' }}">
                    @if($cs->isBirth())<i class="bi bi-balloon-heart me-1"></i>@endif
                    {{ $cs->patient->fullName() }}
                </a>
                @endforeach
            </div>
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
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i>Historial
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white p-3 shadow-sm mb-3">

        {{-- ────────── TAB: Médicos ────────── --}}
        <div class="tab-pane fade show active" id="medics" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-person-badge me-1"></i>Médicos asignados
                </h6>
                @if($user->isAdmin() || $user->isNurse())
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMedico">
                    <i class="bi bi-plus-circle me-1"></i>Agregar médico
                </button>
                @endif
            </div>

            @if($stay->currentDoctors->isEmpty())
                <p class="text-muted fst-italic mb-0">No hay médicos asignados a esta estancia.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Asignado desde</th>
                                @if($user->isAdmin())<th></th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stay->currentDoctors as $sd)
                            <tr>
                                <td>{{ $sd->doctor->fullName() }}</td>
                                <td>{{ $sd->doctor->specialtiesLabel() ?? '—' }}</td>
                                <td>{{ $sd->assigned_at->format('d/m/Y H:i') }}</td>
                                @if($user->isAdmin())
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm py-0 btn-quitar-medico"
                                            data-modal-id="removeDoctor{{ $sd->id }}">
                                        <i class="bi bi-person-dash"></i> Quitar
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Indicaciones médicas (se mantiene la funcionalidad del avance anterior) --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-pencil-square me-1"></i>Indicaciones médicas
                    </h6>
                    @if($user->isNurse() || $user->isAdmin())
                        @if($stay->currentDoctors->isNotEmpty())
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnEscribirInstruccion">
                            <i class="bi bi-plus-circle me-1"></i>Escribir indicación
                        </button>
                        @else
                        <span class="text-muted small fst-italic">
                            <i class="bi bi-info-circle me-1"></i>Asigna un médico para poder registrar indicaciones
                        </span>
                        @endif
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
                                </span>
                                <span class="text-muted small">
                                    {{ $instruction->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <p class="mb-0" style="white-space:pre-line;">{{ $instruction->body }}</p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- ────────── TAB: Hojas de Enfermería ────────── --}}
        <div class="tab-pane fade" id="nursing" role="tabpanel">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Hojas de Enfermería
            </h6>

            {{-- Talla y peso de esta estancia --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
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
                    @if($user->isAdmin() || $user->isNurse())
                        <a href="{{ route('stays.measurements.edit', $stay) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>{{ ($stay->height_cm || $stay->weight_kg) ? 'Editar' : 'Capturar talla y peso' }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Acceso al módulo --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clipboard2-pulse text-primary" style="font-size:2.5rem;"></i>
                    <p class="text-muted mb-3 mt-2">
                        Captura y consulta signos vitales, resumen del turno y demás registros de enfermería.
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
                $canPrescribe = $user->isAdmin()
                    || ($user->isDoctor() && $stay->currentDoctors->where('doctor_id', $user->id)->count() > 0)
                    || ($user->isNurse() && $stay->currentDoctors->count() > 0);
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
                @if($canPrescribe && $stay->isActive())
                    <a href="{{ route('medicationOrders.create', $stay) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-1"></i>Nueva prescripción
                    </a>
                @endif
            </div>
        </div>

        {{-- ────────── TAB: Documentos ────────── --}}
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
                                @if(! $user->isDoctor())<th class="text-end">Acciones</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $sd)
                            @php
                                $doc = $sd->document;
                                // La Nota de egreso solo se llena al dar de alta: bloqueada en estancia activa.
                                $blockedUntilDischarge = $doc->is_universal
                                    && $doc->available_on_discharge
                                    && $stay->isActive();
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
                                @if(! $user->isDoctor())
                                @php
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
                                <td class="text-end text-nowrap">
                                    {{-- Llenar --}}
                                    @if($isTriage)
                                        {{-- Sin formulario de llenado: se genera desde el triage --}}
                                    @elseif($isFrontSheet)
                                        <a href="{{ route('frontSheet.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Llenar
                                        </a>
                                    @elseif($isNursingSheets)
                                        <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Llenar
                                        </a>
                                    @elseif($isAdmissionNote)
                                        {{-- La Nota de Ingreso se alimenta de las indicaciones médicas; no tiene formulario propio. --}}
                                    @elseif($isMedicalHistory)
                                        @if($stayActive)
                                            <a href="{{ route('medicalHistory.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> {{ $isCompleted ? 'Editar' : 'Llenar' }}
                                            </a>
                                        @endif
                                    @elseif($isDischargeNote)
                                        @if($stayActive || $user->isAdmin())
                                            <a href="{{ route('dischargeNote.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> {{ $isCompleted ? 'Editar' : 'Llenar' }}
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                    title="Solo el médico o administrador puede editar tras el alta">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </button>
                                        @endif
                                    @elseif($isAuthorizedConsent)
                                        @if($stayActive)
                                            <a href="{{ route('authorizedConsent.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary" disabled title="Estancia dada de alta">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </button>
                                        @endif
                                    @elseif($isAnesthesiaConsent)
                                        @if($stayActive)
                                            <a href="{{ route('anesthesiaConsent.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary" disabled title="Estancia dada de alta">
                                                <i class="bi bi-pencil"></i> Llenar
                                            </button>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled
                                                title="{{ $blockedUntilDischarge ? 'Disponible al dar de alta' : 'Próximamente' }}">
                                            <i class="bi bi-pencil"></i> Llenar
                                        </button>
                                    @endif

                                    {{-- Ver --}}
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
                                    @elseif($isMedicalHistory && ($isCompleted || \App\Models\MedicalHistory::where('stay_id', $stay->id)->exists()))
                                        <a href="{{ route('medicalHistory.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isDischargeNote && ($isCompleted || \App\Models\DischargeNote::where('stay_id', $stay->id)->exists()))
                                        <a href="{{ route('dischargeNote.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isAuthorizedConsent && $isCompleted)
                                        <a href="{{ route('authorizedConsent.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @elseif($isAnesthesiaConsent && $isCompleted)
                                        <a href="{{ route('anesthesiaConsent.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled
                                                title="{{ ($isAuthorizedConsent || $isAnesthesiaConsent) ? 'Llena el documento primero' : ($isFrontSheet ? 'Llena el documento primero' : 'Próximamente') }}">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    @endif
                                </td>
                                @endif
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
                    @if(! $user->isDoctor())
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
                    @endif
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

        {{-- ────────── TAB: Historial ────────── --}}
        <div class="tab-pane fade" id="history" role="tabpanel">

            {{-- Traslados de cuarto --}}
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-arrow-left-right me-1"></i>Traslados de cuarto
            </h6>
            @if($stay->roomTransfers->isEmpty())
                <p class="text-muted fst-italic">Sin traslados en este ingreso.</p>
            @else
                <div class="table-responsive mb-4">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>De</th><th>A</th><th>Fecha</th><th>Realizado por</th></tr>
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
            @endif

            {{-- Estancias previas del paciente --}}
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-clock-history me-1"></i>Estancias previas de este paciente
            </h6>
            @if($previousStays->isEmpty())
                <p class="text-muted fst-italic mb-0">Sin estancias previas.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cuarto</th>
                                <th>Diagnóstico</th>
                                <th>Ingreso</th>
                                <th>Alta</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previousStays as $prev)
                            <tr>
                                <td>Cuarto {{ $prev->room->number }}</td>
                                <td>{{ Str::limit($prev->diagnosis, 60) }}</td>
                                <td>{{ $prev->admission_date->format('d/m/Y H:i') }}</td>
                                <td>{{ $prev->discharge_date->format('d/m/Y H:i') }}</td>
                                <td>{{ $prev->admission_date->diffForHumans($prev->discharge_date, true) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════ ACCIONES INFERIORES ════════════════ --}}
    <div class="d-flex flex-wrap gap-2">
        @if($user->isAdmin() || $user->isNurse())
        <a href="{{ route('patients.edit', $stay->patient) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Editar paciente
        </a>
        @endif

        @if($user->isNurse())
        <a href="{{ route('roomTransfers.create', $stay) }}" class="btn btn-outline-warning">
            <i class="bi bi-arrow-left-right me-1"></i>Trasladar a otro cuarto
        </a>
        @endif

        @if(($user->isAdmin() || $user->isNurse()) && $room->canRegisterBirth())
        <a href="{{ route('stays.createBirth', $room) }}" class="btn btn-outline-success">
            <i class="bi bi-balloon-heart me-1"></i>Registrar nacimiento
        </a>
        @endif

        @if($user->isAdmin() || $user->isNurse())
        <button type="button" class="btn btn-danger ms-auto" id="btnDarDeAlta">
            <i class="bi bi-box-arrow-up-right me-1"></i>Dar de alta
        </button>
        @endif
    </div>

    {{-- ════════════════ MODALES ════════════════ --}}


    {{-- Modal: Agregar médico (admin + nurse) --}}
    @if($user->isAdmin() || $user->isNurse())
    <div class="modal fade" id="addDoctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('stayDoctors.store', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-plus me-1"></i>Agregar médico a la estancia
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        @if($doctors->isEmpty())
                        <div class="alert alert-warning border-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            No hay médicos activos registrados en el sistema.
                        </div>
                        @else
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label fw-semibold">Médico</label>
                            <select id="doctor_id" name="doctor_id" class="form-select" required>
                                <option value="">— Selecciona un médico —</option>
                                @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->fullName() }}
                                    @if($doc->specialty)
                                        — {{ \App\Enums\DoctorSpecialty::tryFrom($doc->specialty)?->label() }}
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modal_specialty" class="form-label fw-semibold">Especialidad asignada</label>
                            <select id="modal_specialty" name="specialty" class="form-select" required>
                                <option value="">— Selecciona especialidad —</option>
                                @foreach($specialties as $val => $lbl)
                                <option value="{{ $val }}" {{ old('specialty') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        @if($doctors->isNotEmpty())
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Asignar
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modales: Quitar médico (solo admin) --}}
    @if($user->isAdmin())
    @foreach($stay->currentDoctors as $sd)
    <div class="modal fade" id="removeDoctor{{ $sd->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="bi bi-person-dash me-1"></i>Quitar médico
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-2">
                    ¿Quitar a <strong>{{ $sd->doctor->fullName() }}</strong> de esta estancia?
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('stayDoctors.destroy', $sd) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Quitar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Modal: Dar de alta --}}
    @if($user->isAdmin() || $user->isNurse())
    <div class="modal fade" id="dischargeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('stays.discharge', $stay) }}">
                @csrf
                @if(session('warning_discharge_note'))
                    <input type="hidden" name="confirmed_without_note" value="1">
                    <input type="hidden" name="discharge_reason" value="{{ old('discharge_reason', session('pending_discharge_reason')) }}">
                @endif
                <div class="modal-content shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-box-arrow-up-right text-danger me-1"></i>Dar de alta al paciente
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        @if(session('warning_discharge_note'))
                            @php $w = session('warning_discharge_note'); @endphp
                            <div class="alert alert-warning">
                                <strong><i class="bi bi-exclamation-triangle me-1"></i>{{ $w['message'] }}</strong>
                                <ul class="mt-2 mb-2">
                                    @foreach($w['pending'] as $section)
                                        <li>{{ $section }}</li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('dischargeNote.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i>Llenar Nota de Alta ahora
                                </a>
                            </div>
                            <p class="text-muted small">O confirma para ejecutar el alta de todos modos.</p>
                        @else
                            <p>Vas a dar de alta a <strong>{{ $stay->patient->fullName() }}</strong> del Cuarto <strong>{{ $room->number }}</strong>.</p>
                            <p class="text-muted small">
                                Al confirmar, se suspenderán automáticamente todas las órdenes activas
                                (medicamentos, balance de líquidos, monitoreo de glucemia) y se marcarán
                                como completados los documentos correspondientes.
                            </p>
                        @endif

                        @if(!session('warning_discharge_note'))
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Motivo del alta <span class="text-danger">*</span>
                                </label>
                                <select name="discharge_reason" required
                                        class="form-select @error('discharge_reason') is-invalid @enderror">
                                    <option value="">Selecciona un motivo…</option>
                                    @foreach(config('discharge_reasons') as $key => $label)
                                        <option value="{{ $key }}"
                                                {{ old('discharge_reason') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('discharge_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle me-1"></i>
                            @if(session('warning_discharge_note'))
                                Continuar alta sin nota completa
                            @else
                                Confirmar alta
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if($errors->has('discharge_reason') || session('warning_discharge_note'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('dischargeModal')).show();
        });
    </script>
    @endif
    @endif

    {{-- Modal: Escribir indicación (admin + nurse en nombre de médico asignado) --}}
    @if(($user->isNurse() || $user->isAdmin()) && $stay->currentDoctors->isNotEmpty())
    <div class="modal fade" id="instruccionNurseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('stays.storeInstruction', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>Registrar indicación médica
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nurse_doctor_id" class="form-label fw-semibold">
                                Médico que dicta la indicación <span class="text-danger">*</span>
                            </label>
                            <select id="nurse_doctor_id" name="doctor_id"
                                    class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">— Selecciona el médico —</option>
                                @foreach($stay->currentDoctors as $sd)
                                <option value="{{ $sd->doctor_id }}" {{ old('doctor_id') == $sd->doctor_id ? 'selected' : '' }}>
                                    {{ $sd->doctor->fullName() }} — {{ \App\Enums\DoctorSpecialty::from($sd->specialty)->label() }}
                                </option>
                                @endforeach
                            </select>
                            @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-1">
                            <label for="nurse_instruccion_body" class="form-label fw-semibold">
                                Indicación <span class="text-danger">*</span>
                            </label>
                            <textarea id="nurse_instruccion_body" name="body"
                                      class="form-control @error('body') is-invalid @enderror"
                                      rows="6" maxlength="3000"
                                      placeholder="Escribe aquí la indicación dictada por el médico..."
                                      required>{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-end"><span id="nurseCharCount">0</span> / 3,000 caracteres</div>
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
    @endif

</div>{{-- /container --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Anclas de tabs en la URL ──────────────────────────────
    const validHashes = ['#medics', '#nursing', '#indications', '#documents', '#history'];
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

    // ── Modales ───────────────────────────────────────────────
    const openModal = (id) => {
        const el = document.getElementById(id);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    };

    const btnAgregar = document.getElementById('btnAgregarMedico');
    if (btnAgregar) btnAgregar.addEventListener('click', () => openModal('addDoctorModal'));

    document.querySelectorAll('.btn-quitar-medico').forEach(function (btn) {
        btn.addEventListener('click', () => openModal(btn.getAttribute('data-modal-id')));
    });

    const btnAlta = document.getElementById('btnDarDeAlta');
    if (btnAlta) btnAlta.addEventListener('click', () => openModal('dischargeModal'));

    const btnInstruccion = document.getElementById('btnEscribirInstruccion');
    if (btnInstruccion) {
        btnInstruccion.addEventListener('click', () => openModal('instruccionNurseModal'));
        const textarea  = document.getElementById('nurse_instruccion_body');
        const charCount = document.getElementById('nurseCharCount');
        if (textarea && charCount) {
            const update = () => charCount.textContent = textarea.value.length;
            textarea.addEventListener('input', update);
            update();
        }
    }

    // Reabrir modal correspondiente si hubo errores de validación
    @if($errors->hasAny(['doctor_id', 'specialty']) && !$errors->has('body'))
    openModal('addDoctorModal');
    @endif
    @if($errors->has('body') || ($errors->has('doctor_id') && request()->has('body')))
    openModal('instruccionNurseModal');
    @endif

});
</script>
@endpush
