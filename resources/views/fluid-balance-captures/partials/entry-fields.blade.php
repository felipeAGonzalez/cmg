{{--
    Campos numéricos de una toma de balance (6 ingresos + 6 egresos).
    Variable opcional: $prefix (para ids únicos entre modales). Default ''.
--}}
@php
    $prefix = $prefix ?? '';
    $inputs = [
        'oral_ml'         => 'Oral',
        'iv_solution_ml'  => 'Solución IV',
        'blood_ml'        => 'Sangre',
        'plasma_ml'       => 'Plasma',
        'sonda_ml'        => 'Sonda',
        'other_inputs_ml' => 'Otros',
    ];
    $outputs = [
        'urine_ml'        => 'Orina',
        'evacuation_ml'   => 'Evacuación',
        'vomit_ml'        => 'Vómito',
        'hemorrhage_ml'   => 'Hemorragia',
        'suction_ml'      => 'Aspiración',
        'canalization_ml' => 'Canalización',
    ];
@endphp

<h6 class="fw-bold text-success border-bottom pb-1 mb-2"><i class="bi bi-box-arrow-in-down me-1"></i>Ingresos (ml)</h6>
<div class="row g-2 mb-3">
    @foreach($inputs as $field => $label)
        <div class="col-4 col-md-2">
            <label for="{{ $prefix }}{{ $field }}" class="form-label small mb-1">{{ $label }}</label>
            <input type="number" min="0" max="10000" step="1"
                   id="{{ $prefix }}{{ $field }}" name="{{ $field }}"
                   class="form-control form-control-sm" placeholder="0"
                   value="{{ $prefix === '' ? old($field) : '' }}">
        </div>
    @endforeach
</div>

<h6 class="fw-bold text-danger border-bottom pb-1 mb-2"><i class="bi bi-box-arrow-up me-1"></i>Egresos medibles (ml)</h6>
<div class="row g-2 mb-3">
    @foreach($outputs as $field => $label)
        <div class="col-4 col-md-2">
            <label for="{{ $prefix }}{{ $field }}" class="form-label small mb-1">{{ $label }}</label>
            <input type="number" min="0" max="10000" step="1"
                   id="{{ $prefix }}{{ $field }}" name="{{ $field }}"
                   class="form-control form-control-sm" placeholder="0"
                   value="{{ $prefix === '' ? old($field) : '' }}">
        </div>
    @endforeach
</div>

<div class="alert alert-info border-0 py-2 small">
    <i class="bi bi-info-circle me-1"></i>
    Las pérdidas insensibles (resp/sudor) se calculan automáticamente con la fórmula CMG
    según peso, edad y temperatura del paciente.
</div>

<div class="mb-1">
    <label for="{{ $prefix }}observation" class="form-label small fw-semibold mb-1">Observación <span class="text-muted fw-normal">(opcional)</span></label>
    <textarea id="{{ $prefix }}observation" name="observation" rows="2" maxlength="500"
              class="form-control form-control-sm" placeholder="Notas de la toma.">{{ $prefix === '' ? old('observation') : '' }}</textarea>
</div>
