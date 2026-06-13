{{--
    Campos de captura de una toma de signos vitales.
    Variables opcionales:
      $prefix   : para diferenciar ids entre modales (los name no llevan prefijo
                  porque cada modal es un form independiente).
      $showTime : muestra la hora de la toma y la glucemia. true al registrar
                  una toma nueva; false al editar (la hora es inmutable).
--}}
@php
    $prefix   = $prefix ?? '';
    $showTime = $showTime ?? true;
    $activeGlucoseOrder = $showTime ? $stay->activeGlucoseMonitoringOrder() : null;
@endphp

@if($showTime)
<div class="mb-3">
    <label for="{{ $prefix }}recorded_at" class="form-label fw-semibold small">
        Hora de la toma <span class="text-danger">*</span>
    </label>
    <input type="datetime-local" id="{{ $prefix }}recorded_at" name="recorded_at"
           class="form-control @error('recorded_at') is-invalid @enderror"
           value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}"
           max="{{ now()->format('Y-m-d\TH:i') }}" required>
    @error('recorded_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">Por defecto: ahora. Puedes ajustarla si la toma se hizo minutos antes.</small>
</div>
@endif

<div class="row g-3">
    <div class="col-6 col-md-4">
        <label for="{{ $prefix }}heart_rate" class="form-label fw-semibold small">F.C. <span class="text-muted">(lpm)</span></label>
        <input type="number" min="20" max="250" id="{{ $prefix }}heart_rate" name="heart_rate"
               class="form-control" value="{{ old('heart_rate') }}">
    </div>
    <div class="col-6 col-md-4">
        <label for="{{ $prefix }}respiratory_rate" class="form-label fw-semibold small">F.R. <span class="text-muted">(rpm)</span></label>
        <input type="number" min="5" max="80" id="{{ $prefix }}respiratory_rate" name="respiratory_rate"
               class="form-control" value="{{ old('respiratory_rate') }}">
    </div>
    <div class="col-6 col-md-4">
        <label for="{{ $prefix }}temperature" class="form-label fw-semibold small">Temp. <span class="text-muted">(°C)</span></label>
        <input type="number" step="0.1" min="30" max="45" id="{{ $prefix }}temperature" name="temperature"
               class="form-control" value="{{ old('temperature') }}">
    </div>
    <div class="col-6 col-md-4">
        <label for="{{ $prefix }}blood_pressure_systolic" class="form-label fw-semibold small">T.A. sistólica</label>
        <input type="number" min="40" max="300" id="{{ $prefix }}blood_pressure_systolic" name="blood_pressure_systolic"
               class="form-control" value="{{ old('blood_pressure_systolic') }}">
    </div>
    <div class="col-6 col-md-4">
        <label for="{{ $prefix }}blood_pressure_diastolic" class="form-label fw-semibold small">T.A. diastólica</label>
        <input type="number" min="20" max="200" id="{{ $prefix }}blood_pressure_diastolic" name="blood_pressure_diastolic"
               class="form-control" value="{{ old('blood_pressure_diastolic') }}">
    </div>
    <div class="col-12">
        <label for="{{ $prefix }}notes" class="form-label fw-semibold small">Notas</label>
        <input type="text" maxlength="255" id="{{ $prefix }}notes" name="notes"
               class="form-control" value="{{ old('notes') }}">
    </div>
</div>

@if($activeGlucoseOrder)
<div class="mt-3 bg-info-subtle p-3 rounded">
    <label for="{{ $prefix }}glucose_mg_dl" class="form-label fw-semibold small mb-1">
        <i class="bi bi-droplet"></i> Glucemia capilar (mg/dL)
        <span class="text-muted fw-normal">— Indicado por Dr(a). {{ $activeGlucoseOrder->prescribedBy?->fullName() ?? '—' }}</span>
    </label>
    @if($activeGlucoseOrder->schedule_description)
        <small class="d-block text-muted mb-2">Esquema: {{ $activeGlucoseOrder->schedule_description }}</small>
    @endif
    <input type="number" id="{{ $prefix }}glucose_mg_dl" name="glucose_mg_dl"
           min="20" max="800" value="{{ old('glucose_mg_dl') }}"
           class="form-control @error('glucose_mg_dl') is-invalid @enderror" placeholder="Ej. 95">
    @error('glucose_mg_dl')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">
        Opcional. Si capturas un valor, se registrará como lectura de glucemia asociada a esta toma.
    </small>
</div>
@endif
