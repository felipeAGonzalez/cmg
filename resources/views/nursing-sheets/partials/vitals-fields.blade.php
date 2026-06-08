{{--
    Campos de captura de una toma de signos vitales.
    Variable opcional: $prefix (para diferenciar ids entre modales). Los name
    se mantienen sin prefijo porque cada modal es un form independiente.
--}}
@php $prefix = $prefix ?? ''; @endphp

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
