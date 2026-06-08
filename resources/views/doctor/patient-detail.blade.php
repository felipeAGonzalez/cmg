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
                        <span class="badge bg-secondary">Cuarto {{ $stay->room->number }}</span>
                        <span class="badge bg-danger">Ocupado</span>
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
                    </div>
                </div>

                <div class="col-auto">
                    <a href="{{ route('doctor.myPatients') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Mis pacientes
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                <i class="bi bi-prescription2 me-1"></i>Indicaciones
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
                                <td>{{ \App\Enums\DoctorSpecialty::from($sd->specialty)->label() }}</td>
                                <td>{{ $sd->assigned_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Instrucciones médicas --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-pencil-square me-1"></i>Instrucciones médicas
                    </h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnEscribirInstruccion">
                        <i class="bi bi-plus-circle me-1"></i>Escribir instrucción
                    </button>
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

        {{-- ────────── TAB: Indicaciones ────────── --}}
        <div class="tab-pane fade" id="indications" role="tabpanel">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-prescription2 me-1"></i>Indicaciones — Prescripciones
            </h6>

            @php
                $medOrders    = $stay->medicationOrders;
                $medActive    = $medOrders->filter->isActive();
                $medSuspended = $medOrders->filter->isSuspended();
                $medFinished  = $medOrders->filter->isFinished();
                $isAssigned   = $stay->currentDoctors->where('doctor_id', $doctor->id)->count() > 0;
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
                    <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Indicaciones del paciente
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
                                $doc          = $sd->document;
                                $isFrontSheet = $doc->code === 'front_sheet';
                                $isCompleted  = $sd->status === \App\Models\StayDocument::STATUS_COMPLETED;
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
                                    @if($isFrontSheet && $isCompleted)
                                        <a href="{{ route('frontSheet.pdf', $stay) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
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
        </div>
    </div>

    {{-- ════════════════ ACCIONES INFERIORES ════════════════ --}}
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('doctor.myPatients') }}" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Volver a mis pacientes
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
                            <i class="bi bi-pencil-square me-1"></i>Escribir instrucción médica
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
                            <label for="instruccion_body" class="form-label fw-semibold">Instrucción</label>
                            <textarea id="instruccion_body" name="body"
                                      class="form-control @error('body') is-invalid @enderror"
                                      rows="6" maxlength="3000"
                                      placeholder="Escribe aquí la instrucción médica..."
                                      required>{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-end"><span id="charCount">0</span> / 3000 caracteres</div>
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
