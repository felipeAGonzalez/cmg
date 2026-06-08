@extends('layouts.app')

@php $user = auth()->user(); @endphp

@section('content')
<div class="container py-4" style="max-width:820px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-prescription2 me-2"></i>Nueva prescripción — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    {{-- Resumen del paciente --}}
    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="row g-2 small">
            <div class="col-md-6"><span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong></div>
            <div class="col-md-6"><span class="text-muted">Edad:</span> {{ $stay->patient->age() }} años</div>
            <div class="col-12"><span class="text-muted">Diagnóstico:</span> {{ $stay->diagnosis }}</div>
            @if($stay->height_cm || $stay->weight_kg)
            <div class="col-12">
                <span class="text-muted">Talla/Peso:</span>
                {{ $stay->height_cm ? rtrim(rtrim(number_format($stay->height_cm, 2), '0'), '.') . ' cm' : '—' }} ·
                {{ $stay->weight_kg ? rtrim(rtrim(number_format($stay->weight_kg, 2), '0'), '.') . ' kg' : '—' }}
            </div>
            @endif
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @if(! $user->isDoctor() && $availableDoctors->isEmpty())
        {{-- Nurse/admin sin doctores asignados: no se puede prescribir --}}
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-1"></i>
            No hay médicos asignados a este paciente. Asigna un médico antes de registrar una prescripción.
        </div>
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Volver</a>
    @else
    <form method="POST" action="{{ route('medicationOrders.store', $stay) }}">
        @csrf

        {{-- Médico prescriptor --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-person-badge me-1"></i>Médico prescriptor</h6>
            @if($user->isDoctor())
                <input type="hidden" name="prescribed_by_id" value="{{ $user->id }}">
                <p class="mb-0">Prescrita por: <strong>Dr(a). {{ $user->fullName() }}</strong></p>
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

        {{-- Datos de la prescripción --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-capsule me-1"></i>Datos de la prescripción</h6>
            @include('medication-orders.partials.form-fields')
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar prescripción</button>
            <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
@include('medication-orders.partials.toggle-script')
@endpush
