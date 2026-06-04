@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:800px;">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-door-closed me-1"></i>Cuarto {{ $room->number }}
            </h4>
            <span class="badge bg-danger fs-6">Ocupado</span>
        </div>
    </div>

    {{-- Errores de validación (formulario de agregar médico redirige aquí) --}}
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

    {{-- Información del paciente --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-person me-1"></i>Información del paciente
            </h6>
            <div class="row g-2">
                <div class="col-sm-6">
                    <span class="text-muted small">Nombre completo</span>
                    <div class="fw-semibold">{{ $stay->patient->fullName() }}</div>
                </div>
                <div class="col-sm-3">
                    <span class="text-muted small">Edad</span>
                    <div class="fw-semibold">{{ $stay->patient->age() }} años</div>
                </div>
                <div class="col-sm-3">
                    <span class="text-muted small">Género</span>
                    <div class="fw-semibold">{{ $stay->patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</div>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small">Fecha de nacimiento</span>
                    <div class="fw-semibold">{{ $stay->patient->birth_date->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estancia actual --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Estancia actual
            </h6>
            <div class="mb-2">
                <span class="text-muted small">Diagnóstico</span>
                <div class="fw-semibold" style="white-space:pre-line;">{{ $stay->diagnosis }}</div>
            </div>
            <div>
                <span class="text-muted small">Fecha de ingreso</span>
                <div class="fw-semibold">{{ $stay->admission_date->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Médicos asignados --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-person-badge me-1"></i>Médicos asignados
                </h6>
                @if(Auth::user()->isAdmin() || Auth::user()->isNurse())
                <button type="button" class="btn btn-outline-primary btn-sm"
                        id="btnAgregarMedico">
                    <i class="bi bi-plus-circle me-1"></i>Agregar médico
                </button>
                @endif
            </div>

            @if($stay->currentDoctors->isEmpty())
                <p class="text-muted fst-italic mb-0">No hay médicos asignados a esta estancia.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Asignado desde</th>
                                @if(Auth::user()->isAdmin())
                                <th></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stay->currentDoctors as $sd)
                            <tr>
                                <td>{{ $sd->doctor->fullName() }}</td>
                                <td>{{ \App\Enums\DoctorSpecialty::from($sd->specialty)->label() }}</td>
                                <td>{{ $sd->assigned_at->format('d/m/Y H:i') }}</td>
                                @if(Auth::user()->isAdmin())
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm py-0 btn-quitar-medico"
                                            data-modal-id="removeDoctor{{ $sd->id }}">
                                        <i class="bi bi-person-dash"></i> Quitar
                                    </button>
                                </td>
                                @else
                                <td></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Historial de traslados --}}
    @if($stay->roomTransfers->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-arrow-left-right me-1"></i>Historial de traslados en este ingreso
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>De</th><th>A</th><th>Fecha</th><th>Realizado por</th>
                        </tr>
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
    </div>
    @endif

    {{-- Instrucciones médicas --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-pencil-square me-1"></i>Instrucciones médicas
                </h6>
                @if(Auth::user()->isNurse() || Auth::user()->isAdmin())
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

    {{-- Botones de acción --}}
    <div class="d-flex flex-wrap gap-2 mt-3">
        @if(Auth::user()->isAdmin() || Auth::user()->isNurse())
        <a href="{{ route('patients.edit', $stay->patient) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Editar datos del paciente
        </a>
        @endif

        @if(Auth::user()->isNurse())
        <a href="{{ route('roomTransfers.create', $stay) }}" class="btn btn-outline-warning">
            <i class="bi bi-arrow-left-right me-1"></i>Trasladar a otro cuarto
        </a>
        @endif

        @if(Auth::user()->isAdmin() || Auth::user()->isNurse())
        <button type="button" class="btn btn-danger" id="btnDarDeAlta">
            <i class="bi bi-box-arrow-up-right me-1"></i>Dar de alta
        </button>
        @endif

        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-grid-3x3-gap me-1"></i>Volver al tablero
        </a>
    </div>

    {{-- ═══════════════════════════════════════════════
         MODALES — ubicados dentro del container
    ════════════════════════════════════════════════ --}}

    {{-- Modal: Agregar médico (admin + nurse) --}}
    @if(Auth::user()->isAdmin() || Auth::user()->isNurse())
    <div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('stayDoctors.store', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="addDoctorModalLabel">
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
                                <option value="{{ $doc->id }}"
                                    {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
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
                                <option value="{{ $val }}"
                                    {{ old('specialty') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
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
    @if(Auth::user()->isAdmin())
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
    @if(Auth::user()->isAdmin() || Auth::user()->isNurse())
    <div class="modal fade" id="dischargeModal" tabindex="-1" aria-labelledby="dischargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="dischargeModalLabel">Confirmar alta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>
                        ¿Confirmas dar de alta a <strong>{{ $stay->patient->fullName() }}</strong>?
                    </p>
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
    @if((Auth::user()->isNurse() || Auth::user()->isAdmin()) && $stay->currentDoctors->isNotEmpty())
    <div class="modal fade" id="instruccionNurseModal" tabindex="-1"
         aria-labelledby="instruccionNurseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('stays.storeInstruction', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="instruccionNurseModalLabel">
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
                                    class="form-select @error('doctor_id') is-invalid @enderror"
                                    required>
                                <option value="">— Selecciona el médico —</option>
                                @foreach($stay->currentDoctors as $sd)
                                <option value="{{ $sd->doctor_id }}"
                                    {{ old('doctor_id') == $sd->doctor_id ? 'selected' : '' }}>
                                    {{ $sd->doctor->fullName() }}
                                    — {{ \App\Enums\DoctorSpecialty::from($sd->specialty)->label() }}
                                </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-end">
                                <span id="nurseCharCount">0</span> / 3,000 caracteres
                            </div>
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

    // Botón "Agregar médico"
    const btnAgregar = document.getElementById('btnAgregarMedico');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('addDoctorModal')
            ).show();
        });
    }

    // Botones "Quitar médico"
    document.querySelectorAll('.btn-quitar-medico').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modalId = btn.getAttribute('data-modal-id');
            const el = document.getElementById(modalId);
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    });

    // Botón "Dar de alta"
    const btnAlta = document.getElementById('btnDarDeAlta');
    if (btnAlta) {
        btnAlta.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('dischargeModal')
            ).show();
        });
    }

    // Botón "Escribir instrucción" (enfermero)
    const btnInstruccion = document.getElementById('btnEscribirInstruccion');
    if (btnInstruccion) {
        btnInstruccion.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('instruccionNurseModal')
            ).show();
        });

        // Contador de caracteres
        const textarea  = document.getElementById('nurse_instruccion_body');
        const charCount = document.getElementById('nurseCharCount');
        if (textarea && charCount) {
            const update = () => charCount.textContent = textarea.value.length;
            textarea.addEventListener('input', update);
            update();
        }
    }

    // Reabrir modal de agregar médico si hubo error de validación en ese form
    @if($errors->hasAny(['doctor_id', 'specialty']) && !$errors->has('body'))
    const addModal = document.getElementById('addDoctorModal');
    if (addModal) bootstrap.Modal.getOrCreateInstance(addModal).show();
    @endif

    // Reabrir modal de instrucción si hubo error de validación en ese form
    @if($errors->has('body') || ($errors->has('doctor_id') && request()->has('body')))
    const instrModal = document.getElementById('instruccionNurseModal');
    if (instrModal) bootstrap.Modal.getOrCreateInstance(instrModal).show();
    @endif

});
</script>
@endpush
