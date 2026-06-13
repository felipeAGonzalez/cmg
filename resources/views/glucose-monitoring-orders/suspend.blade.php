@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $order->stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-pause-circle me-2"></i>Suspender monitoreo de glucemia capilar
        </h4>
    </div>

    {{-- Resumen de la orden --}}
    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="row g-2 small">
            <div class="col-md-6"><span class="text-muted">Paciente:</span> <strong>{{ $order->stay->patient->fullName() }}</strong></div>
            <div class="col-md-6"><span class="text-muted">Cuarto:</span> {{ $order->stay->room->number }}</div>
            <div class="col-md-6"><span class="text-muted">Fecha de inicio:</span> {{ $order->start_date->format('d/m/Y') }}</div>
            <div class="col-md-6"><span class="text-muted">Médico prescriptor:</span> Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}</div>
            @if($order->schedule_description)
            <div class="col-12"><span class="text-muted">Esquema:</span> {{ $order->schedule_description }}</div>
            @endif
            @if($order->clinical_reason)
            <div class="col-12"><span class="text-muted">Motivo clínico:</span> {{ $order->clinical_reason }}</div>
            @endif
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="alert alert-warning border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Las lecturas de glucemia ya capturadas se mantendrán como histórico. Si en un futuro se
        requiere reanudar el monitoreo, se deberá crear una nueva orden.
    </div>

    <form method="POST" action="{{ route('glucoseMonitoringOrders.suspend', $order) }}">
        @csrf

        <div class="card border-0 shadow-sm p-4 mb-4">
            <label for="suspension_reason" class="form-label fw-semibold">Motivo de la suspensión</label>
            <textarea id="suspension_reason" name="suspension_reason" rows="4" minlength="5" maxlength="500"
                      class="form-control @error('suspension_reason') is-invalid @enderror"
                      placeholder="Indica por qué se suspende el monitoreo de glucemia." required>{{ old('suspension_reason') }}</textarea>
            @error('suspension_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning"><i class="bi bi-pause-circle me-1"></i>Confirmar suspensión</button>
            <a href="{{ route('medicationOrders.index', $order->stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
