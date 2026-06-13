@extends('layouts.app')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="container py-4" style="max-width:820px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-droplet me-2"></i>Iniciar monitoreo de glucemia capilar — {{ $stay->patient->fullName() }}
        </h4>
    </div>

    {{-- Resumen del paciente --}}
    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="row g-2 small">
            <div class="col-md-6"><span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong></div>
            <div class="col-md-6"><span class="text-muted">Edad:</span> {{ $stay->patient->age() }} años · Cuarto {{ $stay->room->number }}</div>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bi bi-info-circle me-1"></i>
        El monitoreo de glucemia capilar se realiza en pacientes diabéticos o con descontrol de glucosa.
        Cuando esté activa la orden, las enfermeras verán un campo extra de glucemia en la captura de signos vitales.
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @if(! $user->isDoctor() && $availableDoctors->isEmpty())
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-1"></i>
            No hay médicos asignados a este paciente. Asigna un médico antes de iniciar el monitoreo.
        </div>
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Volver</a>
    @else
    <form method="POST" action="{{ route('glucoseMonitoringOrders.store', $stay) }}">
        @csrf

        {{-- Médico prescriptor --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-person-badge me-1"></i>Médico prescriptor</h6>
            @if($user->isDoctor())
                <input type="hidden" name="prescribed_by_id" value="{{ $user->id }}">
                <p class="mb-0">Prescrito por: <strong>Dr(a). {{ $user->fullName() }}</strong></p>
            @else
                <label for="prescribed_by_id" class="form-label fw-semibold">Médico que prescribe</label>
                <select id="prescribed_by_id" name="prescribed_by_id"
                        class="form-select @error('prescribed_by_id') is-invalid @enderror" required>
                    <option value="">— Selecciona —</option>
                    @foreach($availableDoctors as $doc)
                        <option value="{{ $doc->id }}" {{ old('prescribed_by_id') == $doc->id ? 'selected' : '' }}>
                            Dr(a). {{ $doc->fullName() }}
                        </option>
                    @endforeach
                </select>
                @error('prescribed_by_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @endif
        </div>

        {{-- Datos del monitoreo --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-droplet me-1"></i>Datos del monitoreo</h6>

            <div class="mb-3">
                <label for="start_date" class="form-label fw-semibold">Fecha de inicio</label>
                <input type="date" id="start_date" name="start_date"
                       class="form-control @error('start_date') is-invalid @enderror"
                       value="{{ old('start_date', now()->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}" required>
                <div class="form-text">Puede ser hoy o una fecha pasada. No puede ser futura.</div>
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="schedule_description" class="form-label fw-semibold">Esquema <span class="text-muted fw-normal">(opcional)</span></label>
                <input type="text" id="schedule_description" name="schedule_description" maxlength="200"
                       class="form-control @error('schedule_description') is-invalid @enderror"
                       value="{{ old('schedule_description') }}"
                       placeholder="Ej. Tres veces al día (07:00, 13:00, 19:00) o cada 4 horas.">
                @error('schedule_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-1">
                <label for="clinical_reason" class="form-label fw-semibold">Motivo clínico <span class="text-muted fw-normal">(opcional)</span></label>
                <textarea id="clinical_reason" name="clinical_reason" rows="3" maxlength="500"
                          class="form-control @error('clinical_reason') is-invalid @enderror"
                          placeholder="Ej. Paciente diabético tipo 2 con descontrol.">{{ old('clinical_reason') }}</textarea>
                @error('clinical_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Iniciar monitoreo</button>
            <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
    @endif
</div>
@endsection
