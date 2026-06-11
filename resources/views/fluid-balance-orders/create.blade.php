@extends('layouts.app')

@php
    $user = auth()->user();
    $needsMeasurements = ! $stay->height_cm || ! $stay->weight_kg;
@endphp

@section('content')
<div class="container py-4" style="max-width:820px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-droplet-half me-2"></i>Iniciar balance de líquidos — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    {{-- Resumen del paciente --}}
    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="row g-2 small">
            <div class="col-md-6"><span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong></div>
            <div class="col-md-6"><span class="text-muted">Edad:</span> {{ $stay->patient->age() }} años</div>
            @if($stay->height_cm || $stay->weight_kg)
            <div class="col-12">
                <span class="text-muted">Talla/Peso:</span>
                {{ $stay->height_cm ? rtrim(rtrim(number_format($stay->height_cm, 2), '0'), '.') . ' cm' : '—' }} ·
                {{ $stay->weight_kg ? rtrim(rtrim(number_format($stay->weight_kg, 2), '0'), '.') . ' kg' : '—' }}
            </div>
            @endif
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bi bi-info-circle me-1"></i>
        El balance de líquidos solo se realiza en pacientes con indicación médica específica
        (pacientes nefróticos, postoperatorios, retención de líquidos, monitoreo intensivo, etc.).
        Una vez iniciado, las enfermeras podrán registrar ingresos y egresos hora por hora.
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
            No hay médicos asignados a este paciente. Asigna un médico antes de iniciar el balance.
        </div>
        <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Volver</a>
    @else
    <div class="position-relative">
        @if($needsMeasurements)
            {{-- Overlay: bloquea el form hasta capturar talla/peso --}}
            <div class="position-absolute top-0 start-0 w-100 h-100"
                 style="background:rgba(255,255,255,.75);z-index:5;cursor:not-allowed;"></div>
        @endif
    <form method="POST" action="{{ route('fluidBalanceOrders.store', $stay) }}" {{ $needsMeasurements ? 'inert' : '' }}>
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

        {{-- Datos del balance --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-droplet me-1"></i>Datos del balance</h6>

            <div class="mb-3">
                <label for="start_date" class="form-label fw-semibold">Fecha de inicio</label>
                <input type="date" id="start_date" name="start_date"
                       class="form-control @error('start_date') is-invalid @enderror"
                       value="{{ old('start_date', now()->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}" required>
                <div class="form-text">Puede ser hoy o una fecha pasada (registros retrasados). No puede ser futura.</div>
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-1">
                <label for="clinical_reason" class="form-label fw-semibold">Motivo clínico <span class="text-muted fw-normal">(opcional)</span></label>
                <textarea id="clinical_reason" name="clinical_reason" rows="3" maxlength="500"
                          class="form-control @error('clinical_reason') is-invalid @enderror"
                          placeholder="Ej. Paciente nefrótico con monitoreo de retención de líquidos.">{{ old('clinical_reason') }}</textarea>
                @error('clinical_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Iniciar balance</button>
            <a href="{{ route('medicationOrders.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
    </div>{{-- /position-relative --}}
    @endif
</div>

{{-- ════════ MODAL: datos requeridos del paciente (talla/peso) ════════ --}}
@if($needsMeasurements && ! ($user && ! $user->isDoctor() && $availableDoctors->isEmpty()))
<div class="modal fade" id="measurementsModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-rulers me-1"></i>Datos requeridos del paciente</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Para iniciar el balance de líquidos es necesario capturar la talla y el peso
                    del paciente. Estos datos se usan para calcular las pérdidas insensibles.
                </p>
                <div id="measurementsError" class="alert alert-danger py-2 d-none"></div>
                <div class="mb-3">
                    <label for="m_height_cm" class="form-label fw-semibold">Talla (cm)</label>
                    <input type="number" step="0.01" min="20" max="250" id="m_height_cm"
                           class="form-control" value="{{ $stay->height_cm }}" placeholder="Ej. 170">
                </div>
                <div class="mb-1">
                    <label for="m_weight_kg" class="form-label fw-semibold">Peso (kg)</label>
                    <input type="number" step="0.01" min="0.5" max="500" id="m_weight_kg"
                           class="form-control" value="{{ $stay->weight_kg }}" placeholder="Ej. 70">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" id="measurementsCancel">Cancelar</button>
                <button type="button" class="btn btn-primary" id="measurementsSave">
                    <i class="bi bi-save me-1"></i>Guardar y continuar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if($needsMeasurements && ! ($user && ! $user->isDoctor() && $availableDoctors->isEmpty()))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('measurementsModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    const errBox = document.getElementById('measurementsError');
    const saveBtn = document.getElementById('measurementsSave');

    document.getElementById('measurementsCancel').addEventListener('click', function () {
        window.location = '{{ route('medicationOrders.index', $stay) }}';
    });

    saveBtn.addEventListener('click', function () {
        const height = document.getElementById('m_height_cm').value;
        const weight = document.getElementById('m_weight_kg').value;
        errBox.classList.add('d-none');

        if (!height || !weight) {
            errBox.textContent = 'Captura tanto la talla como el peso.';
            errBox.classList.remove('d-none');
            return;
        }

        saveBtn.disabled = true;
        const body = new FormData();
        body.append('_method', 'PUT');
        body.append('height_cm', height);
        body.append('weight_kg', weight);

        fetch('{{ route('stays.measurements.update', $stay) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: body,
        })
        .then(async (res) => {
            if (res.ok) { window.location.reload(); return; }
            const data = await res.json().catch(() => ({}));
            let msg = 'No se pudieron guardar los datos. Verifica los valores.';
            if (data.errors) { msg = Object.values(data.errors).flat().join(' '); }
            throw new Error(msg);
        })
        .catch((e) => {
            errBox.textContent = e.message;
            errBox.classList.remove('d-none');
            saveBtn.disabled = false;
        });
    });
});
</script>
@endif
@endpush
