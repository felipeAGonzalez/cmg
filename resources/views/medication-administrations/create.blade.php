@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-check-circle me-2"></i>Registrar administración — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="small">
            <span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong>
            <span class="text-muted ms-2">· {{ $stay->patient->age() }} años</span>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('medicationAdministrations.store', $stay) }}">
        @csrf

        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-capsule me-1"></i>Prescripción</h6>

            <div class="mb-3">
                <label for="medication_order_id" class="form-label fw-semibold">Medicamento prescrito</label>
                <select id="medication_order_id" name="medication_order_id"
                        class="form-select @error('medication_order_id') is-invalid @enderror" required>
                    <option value="">— Selecciona —</option>
                    @foreach($availableOrders as $order)
                        <option value="{{ $order->id }}"
                                data-medication="{{ $order->medication_name }}"
                                data-dose="{{ $order->dose }}"
                                data-route="{{ $order->routeLabel() }}"
                                data-frequency="{{ $order->frequencyLabel() }}"
                                {{ (string) old('medication_order_id', $selectedOrderId) === (string) $order->id ? 'selected' : '' }}>
                            {{ $order->medication_name }} · {{ $order->dose }}
                        </option>
                    @endforeach
                </select>
                @error('medication_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Detalle de la prescripción seleccionada --}}
            <div id="orderDetail" class="p-2 bg-light rounded small text-muted" style="display:none;">
                <span class="text-muted">Dosis prescrita:</span> <strong id="detailDose">—</strong> ·
                <span class="text-muted">Vía:</span> <span id="detailRoute">—</span> ·
                <span class="text-muted">Frecuencia:</span> <span id="detailFrequency">—</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-clipboard-check me-1"></i>Registro</h6>

            <div class="mb-3">
                <label for="administered_at" class="form-label fw-semibold">Fecha y hora de administración</label>
                <input type="datetime-local" id="administered_at" name="administered_at"
                       class="form-control @error('administered_at') is-invalid @enderror"
                       value="{{ old('administered_at', now()->format('Y-m-d\TH:i')) }}"
                       max="{{ now()->format('Y-m-d\TH:i') }}" required>
                <div class="form-text">Usa la hora real en que se administró (no puede ser futura).</div>
                @error('administered_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @include('medication-administrations.partials.result-fields')
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Registrar administración</button>
            <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const select = document.getElementById('medication_order_id');
        const detail = document.getElementById('orderDetail');
        const dose   = document.getElementById('actual_dose');

        const apply = (prefillDose) => {
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) { detail.style.display = 'none'; return; }
            document.getElementById('detailDose').textContent = opt.dataset.dose || '—';
            document.getElementById('detailRoute').textContent = opt.dataset.route || '—';
            document.getElementById('detailFrequency').textContent = opt.dataset.frequency || '—';
            detail.style.display = 'block';
            if (prefillDose && !dose.value) dose.value = opt.dataset.dose || '';
        };

        select.addEventListener('change', () => apply(true));
        apply(false); // estado inicial (sin sobreescribir dosis precargada)
    })();
</script>
@include('medication-administrations.partials.toggle-script')
@endpush
