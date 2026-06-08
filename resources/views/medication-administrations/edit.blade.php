@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Editar administración — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('medicationAdministrations.update', $administration) }}">
        @csrf
        @method('PUT')

        {{-- Prescripción y hora: solo lectura --}}
        <div class="card border-0 shadow-sm p-4 mb-4 bg-light">
            <div class="row g-2 small">
                <div class="col-md-6"><span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong></div>
                <div class="col-md-6"><span class="text-muted">Medicamento:</span> <strong>{{ $administration->medicationOrder->medication_name }}</strong> · {{ $administration->medicationOrder->dose }}</div>
                <div class="col-md-6"><span class="text-muted">Administrada el:</span> {{ $administration->administered_at->format('d/m/Y H:i') }}</div>
                <div class="col-md-6"><span class="text-muted">Turno:</span> {{ $administration->shiftLabel() }}</div>
            </div>
            <div class="form-text mt-2">
                <i class="bi bi-info-circle"></i>
                La prescripción y la hora no se pueden cambiar. Si hubo un error grave, elimina y registra de nuevo.
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-clipboard-check me-1"></i>Registro</h6>
            @include('medication-administrations.partials.result-fields')
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar cambios</button>
            <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('medication-administrations.partials.toggle-script')
@endpush
