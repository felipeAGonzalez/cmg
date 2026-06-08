@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:640px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $order->stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-pause-circle me-2"></i>Suspender prescripción
        </h4>
    </div>

    {{-- Resumen de la prescripción --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">{{ $order->medication_name }} · {{ $order->dose }}</h6>
        <dl class="row mb-0 small">
            <dt class="col-sm-4 text-muted fw-normal">Vía</dt>
            <dd class="col-sm-8">{{ $order->routeLabel() }}</dd>
            <dt class="col-sm-4 text-muted fw-normal">Frecuencia</dt>
            <dd class="col-sm-8">{{ $order->frequencyLabel() }}</dd>
            <dt class="col-sm-4 text-muted fw-normal">Médico prescriptor</dt>
            <dd class="col-sm-8">Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}</dd>
            <dt class="col-sm-4 text-muted fw-normal">Fecha de inicio</dt>
            <dd class="col-sm-8">{{ $order->start_date->format('d/m/Y') }}</dd>
        </dl>
    </div>

    <div class="alert alert-danger border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Esta acción no se puede revertir desde la interfaz.
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('medicationOrders.suspend', $order) }}">
        @csrf

        <div class="card border-0 shadow-sm p-4 mb-4">
            <label for="suspension_reason" class="form-label fw-semibold">Motivo de la suspensión</label>
            <textarea id="suspension_reason" name="suspension_reason" rows="4" minlength="5" maxlength="500"
                      class="form-control @error('suspension_reason') is-invalid @enderror"
                      required>{{ old('suspension_reason') }}</textarea>
            @error('suspension_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger"><i class="bi bi-pause-circle me-1"></i>Confirmar suspensión</button>
            <a href="{{ route('medicationOrders.index', $order->stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
