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
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
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

    {{-- Historial de estancias --}}
    <h5 class="fw-bold mb-3">
        <i class="bi bi-clock-history me-2"></i>Historial de estancias
        <span class="badge bg-secondary ms-1">{{ $patient->stays->count() }}</span>
    </h5>

    @forelse($patient->stays as $stay)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-door-closed me-1"></i>Cuarto {{ $stay->room->number }}
                — {{ $stay->admission_date->format('d/m/Y') }}
            </span>
            @if($stay->isActive())
                <span class="badge bg-danger">Activa</span>
            @else
                <span class="badge bg-secondary">Alta: {{ $stay->discharge_date->format('d/m/Y H:i') }}</span>
            @endif
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
            <div>
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
        </div>
    </div>
    @empty
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-1"></i>Este paciente no tiene estancias registradas.
        </div>
    @endforelse
</div>
@endsection
