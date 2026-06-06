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
                                <td>{{ \App\Enums\DoctorSpecialty::from($sd->specialty)->label() }}</td>
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

            {{-- Instrucciones médicas (se mantiene la funcionalidad del avance anterior) --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-pencil-square me-1"></i>Instrucciones médicas
                    </h6>
                    @if($user->isNurse() || $user->isAdmin())
                        @if($stay->currentDoctors->isNotEmpty())
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnEscribirInstruccion">
                            <i class="bi bi-plus-circle me-1"></i>Escribir instrucción
                        </button>
                        @else
                        <span class="text-muted small fst-italic">
                            <i class="bi bi-info-circle me-1"></i>Asigna un médico para poder registrar instrucciones
                        </span>
                        @endif
                    @endif
                </div>

                @if($stay->instructions->isEmpty())
                    <p class="text-muted fst-italic mb-0">No hay instrucciones registradas para esta estancia.</p>
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
                                    $isFrontSheet = $doc->code === 'front_sheet';
                                    $isCompleted  = $sd->status === \App\Models\StayDocument::STATUS_COMPLETED;
                                @endphp
                                <td class="text-end text-nowrap">
                                    {{-- Llenar --}}
                                    @if($isFrontSheet)
                                        <a href="{{ route('frontSheet.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Llenar
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled
                                                title="{{ $blockedUntilDischarge ? 'Disponible al dar de alta' : 'Próximamente' }}">
                                            <i class="bi bi-pencil"></i> Llenar
                                        </button>
                                    @endif

                                    {{-- Ver --}}
                                    @if($isFrontSheet && $isCompleted)
                                        <a href="{{ route('frontSheet.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled
                                                title="{{ $isFrontSheet ? 'Llena el documento primero' : 'Próximamente' }}">
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
            <div class="modal-content shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Confirmar alta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Confirmas dar de alta a <strong>{{ $stay->patient->fullName() }}</strong>?</p>
                    <p class="text-muted small mb-0">
                        El Cuarto {{ $room->number }} quedará disponible.
                        Esta acción se registrará con la fecha y hora actuales.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('stays.discharge', $stay) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Dar de alta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Escribir instrucción (admin + nurse en nombre de médico asignado) --}}
    @if(($user->isNurse() || $user->isAdmin()) && $stay->currentDoctors->isNotEmpty())
    <div class="modal fade" id="instruccionNurseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('stays.storeInstruction', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>Registrar instrucción médica
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nurse_doctor_id" class="form-label fw-semibold">
                                Médico que dicta la instrucción <span class="text-danger">*</span>
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
                                Instrucción <span class="text-danger">*</span>
                            </label>
                            <textarea id="nurse_instruccion_body" name="body"
                                      class="form-control @error('body') is-invalid @enderror"
                                      rows="6" maxlength="3000"
                                      placeholder="Escribe aquí la instrucción dictada por el médico..."
                                      required>{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-end"><span id="nurseCharCount">0</span> / 3,000 caracteres</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>Guardar instrucción
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
    const validHashes = ['#medics', '#documents', '#history'];
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
