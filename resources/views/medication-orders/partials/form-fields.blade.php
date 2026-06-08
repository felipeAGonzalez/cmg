{{--
    Campos clínicos de una prescripción (compartidos por create y edit).
    Variables: $routes, $frequencies. Opcional: $order (para precargar).
--}}
@php $order = $order ?? null; @endphp

<div class="row g-3">
    <div class="col-md-8">
        <label for="medication_name" class="form-label fw-semibold">Medicamento</label>
        <input type="text" id="medication_name" name="medication_name" maxlength="150"
               class="form-control @error('medication_name') is-invalid @enderror"
               value="{{ old('medication_name', $order->medication_name ?? '') }}" required>
        @error('medication_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="dose" class="form-label fw-semibold">Dosis</label>
        <input type="text" id="dose" name="dose" maxlength="80"
               class="form-control @error('dose') is-invalid @enderror"
               value="{{ old('dose', $order->dose ?? '') }}"
               placeholder="Ej. 500 mg / 1 tableta / 10 ml" required>
        @error('dose')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="route" class="form-label fw-semibold">Vía de administración</label>
        <select id="route" name="route" class="form-select @error('route') is-invalid @enderror" required>
            <option value="">— Selecciona —</option>
            @foreach($routes as $value => $label)
                <option value="{{ $value }}" {{ old('route', $order->route ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('route')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6" id="routeOtherWrapper" style="display:none;">
        <label for="route_other" class="form-label fw-semibold">Especifica la vía</label>
        <input type="text" id="route_other" name="route_other" maxlength="100"
               class="form-control @error('route_other') is-invalid @enderror"
               value="{{ old('route_other', $order->route_other ?? '') }}">
        @error('route_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="frequency" class="form-label fw-semibold">Frecuencia</label>
        <select id="frequency" name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
            <option value="">— Selecciona —</option>
            @foreach($frequencies as $value => $label)
                <option value="{{ $value }}" {{ old('frequency', $order->frequency ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6" id="frequencyOtherWrapper" style="display:none;">
        <label for="frequency_other" class="form-label fw-semibold">Especifica la frecuencia</label>
        <input type="text" id="frequency_other" name="frequency_other" maxlength="100"
               class="form-control @error('frequency_other') is-invalid @enderror"
               value="{{ old('frequency_other', $order->frequency_other ?? '') }}">
        @error('frequency_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="start_date" class="form-label fw-semibold">Fecha de inicio</label>
        <input type="date" id="start_date" name="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', isset($order) ? $order->start_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="duration_days" class="form-label fw-semibold">Duración <span class="text-muted fw-normal">(días, opcional)</span></label>
        <input type="number" id="duration_days" name="duration_days" min="1" max="365"
               class="form-control @error('duration_days') is-invalid @enderror"
               value="{{ old('duration_days', $order->duration_days ?? '') }}" placeholder="Días">
        <div class="form-text">Deja vacío si no aplica duración (hasta nueva orden / PRN sin fin claro).</div>
        @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="indications" class="form-label fw-semibold">Indicaciones <span class="text-muted fw-normal">(opcional)</span></label>
        <textarea id="indications" name="indications" rows="3" maxlength="1000"
                  class="form-control @error('indications') is-invalid @enderror">{{ old('indications', $order->indications ?? '') }}</textarea>
        @error('indications')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
