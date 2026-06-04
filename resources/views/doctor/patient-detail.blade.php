@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:800px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('doctor.myPatients') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-door-closed me-1"></i>Cuarto {{ $stay->room->number }}
        </h4>
        <span class="badge bg-danger fs-6">Ocupado</span>
    </div>

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

    {{-- Médicos asignados (solo lectura) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-person-badge me-1"></i>Médicos asignados
            </h6>
            @if($stay->currentDoctors->isEmpty())
                <p class="text-muted fst-italic mb-0">No hay médicos asignados actualmente.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
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
        </div>
    </div>

    {{-- Instrucciones médicas --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
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

    {{-- Historial de traslados (solo lectura) --}}
    @if($stay->roomTransfers->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-arrow-left-right me-1"></i>Historial de traslados
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>De</th><th>A</th><th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stay->roomTransfers as $transfer)
                        <tr>
                            <td>Cuarto {{ $transfer->fromRoom->number }}</td>
                            <td>Cuarto {{ $transfer->toRoom->number }}</td>
                            <td>{{ $transfer->transferred_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Botones --}}
    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('doctor.myPatients') }}" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Volver a mis pacientes
        </a>
    </div>

    {{-- Modal: Escribir instrucción --}}
    <div class="modal fade" id="instruccionModal" tabindex="-1"
         aria-labelledby="instruccionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow">
                <form method="POST" action="{{ route('doctor.storeInstruction', $stay) }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="instruccionModalLabel">
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
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-end">
                                <span id="charCount">0</span> / 3000 caracteres
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

</div>{{-- /container --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const btnEscribir = document.getElementById('btnEscribirInstruccion');
    const modalEl     = document.getElementById('instruccionModal');

    if (btnEscribir && modalEl) {
        btnEscribir.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    }

    // Contador de caracteres
    const textarea  = document.getElementById('instruccion_body');
    const charCount = document.getElementById('charCount');
    if (textarea && charCount) {
        const update = () => charCount.textContent = textarea.value.length;
        textarea.addEventListener('input', update);
        update();
    }

    // Si hay errores de validación, reabrir el modal automáticamente
    @if($errors->has('body'))
    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
    @endif

});
</script>
@endpush
