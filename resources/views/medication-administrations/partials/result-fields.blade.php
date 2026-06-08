{{--
    Campos del resultado de una administración (compartidos por create y edit).
    Variables: $statuses. Opcional: $administration (para precargar).
--}}
@php
    $administration = $administration ?? null;
    $currentStatus  = old('status', $administration->status ?? 'administered');
@endphp

<div class="mb-3">
    <label for="actual_dose" class="form-label fw-semibold">Dosis administrada</label>
    <input type="text" id="actual_dose" name="actual_dose" maxlength="80"
           class="form-control @error('actual_dose') is-invalid @enderror"
           value="{{ old('actual_dose', $administration->actual_dose ?? '') }}"
           placeholder="Ej. 500 mg / 1 tableta / 10 ml" required>
    @error('actual_dose')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold d-block">Estado</label>
    @foreach($statuses as $value => $label)
        <div class="form-check form-check-inline">
            <input class="form-check-input status-radio" type="radio" name="status"
                   id="status_{{ $value }}" value="{{ $value }}"
                   {{ $currentStatus === $value ? 'checked' : '' }}>
            <label class="form-check-label" for="status_{{ $value }}">{{ $label }}</label>
        </div>
    @endforeach
    @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="mb-3" id="reasonWrapper" style="display:none;">
    <label for="reason" class="form-label fw-semibold">Motivo</label>
    <textarea id="reason" name="reason" rows="2" minlength="3" maxlength="500"
              class="form-control @error('reason') is-invalid @enderror"
              placeholder="Obligatorio cuando la dosis no fue administrada.">{{ old('reason', $administration->reason ?? '') }}</textarea>
    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="observations" class="form-label fw-semibold">Observaciones <span class="text-muted fw-normal">(opcional)</span></label>
    <textarea id="observations" name="observations" rows="2" maxlength="1000"
              class="form-control @error('observations') is-invalid @enderror">{{ old('observations', $administration->observations ?? '') }}</textarea>
    @error('observations')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
