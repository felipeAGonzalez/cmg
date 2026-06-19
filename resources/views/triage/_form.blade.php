{{-- Datos administrativos --}}
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-card-heading me-1"></i>Datos administrativos</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Folio</label>
                <input type="text" name="folio" maxlength="50"
                       value="{{ old('folio', $triage->folio ?? '') }}"
                       class="form-control @error('folio') is-invalid @enderror">
                @error('folio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">
                    Fecha y hora de inicio de evaluaci&oacute;n
                    <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" name="evaluation_started_at" required
                       value="{{ old('evaluation_started_at', isset($triage) ? $triage->evaluation_started_at->format('Y-m-d\TH:i') : $now->format('Y-m-d\TH:i')) }}"
                       class="form-control @error('evaluation_started_at') is-invalid @enderror">
                @error('evaluation_started_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha y hora de t&eacute;rmino (opcional)</label>
                <input type="datetime-local" name="evaluation_ended_at"
                       value="{{ old('evaluation_ended_at', isset($triage) && $triage->evaluation_ended_at ? $triage->evaluation_ended_at->format('Y-m-d\TH:i') : '') }}"
                       class="form-control @error('evaluation_ended_at') is-invalid @enderror">
                @error('evaluation_ended_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Signos vitales --}}
<div class="card mb-3">
    <div class="card-header bg-info-subtle">
        <h6 class="mb-0"><i class="bi bi-heart-pulse me-1"></i>Signos vitales</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Frecuencia card&iacute;aca (lpm)</label>
                <input type="number" name="heart_rate" min="0" max="300"
                       value="{{ old('heart_rate', $triage->heart_rate ?? '') }}"
                       class="form-control @error('heart_rate') is-invalid @enderror">
                @error('heart_rate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">T.A. Sist&oacute;lica</label>
                <input type="number" name="blood_pressure_systolic" min="0" max="300"
                       value="{{ old('blood_pressure_systolic', $triage->blood_pressure_systolic ?? '') }}"
                       class="form-control @error('blood_pressure_systolic') is-invalid @enderror">
                @error('blood_pressure_systolic')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">T.A. Diast&oacute;lica</label>
                <input type="number" name="blood_pressure_diastolic" min="0" max="200"
                       value="{{ old('blood_pressure_diastolic', $triage->blood_pressure_diastolic ?? '') }}"
                       class="form-control @error('blood_pressure_diastolic') is-invalid @enderror">
                @error('blood_pressure_diastolic')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Frecuencia respiratoria</label>
                <input type="number" name="respiratory_rate" min="0" max="100"
                       value="{{ old('respiratory_rate', $triage->respiratory_rate ?? '') }}"
                       class="form-control @error('respiratory_rate') is-invalid @enderror">
                @error('respiratory_rate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Temperatura (&deg;C)</label>
                <input type="number" step="0.1" name="temperature" min="25" max="45"
                       value="{{ old('temperature', $triage->temperature ?? '') }}"
                       class="form-control @error('temperature') is-invalid @enderror">
                @error('temperature')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Saturaci&oacute;n O&sub2; (%)</label>
                <input type="number" name="oxygen_saturation" min="0" max="100"
                       value="{{ old('oxygen_saturation', $triage->oxygen_saturation ?? '') }}"
                       class="form-control @error('oxygen_saturation') is-invalid @enderror">
                @error('oxygen_saturation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Glucemia capilar (mg/dL)</label>
                <input type="number" name="glucose_mg_dl" min="0" max="1000"
                       value="{{ old('glucose_mg_dl', $triage->glucose_mg_dl ?? '') }}"
                       class="form-control @error('glucose_mg_dl') is-invalid @enderror">
                @error('glucose_mg_dl')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Requiere atención inmediata --}}
<div class="card mb-3 border-danger">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Requiere atenci&oacute;n inmediata
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Si cualquiera de estos criterios es <strong>S&iacute;</strong>,
            el paciente se clasifica autom&aacute;ticamente como
            <strong>ROJO (Reanimaci&oacute;n)</strong>.
        </p>
        <div class="row g-3">
            @php
                $immediates = [
                    'immediate_alert_loss' => 'Pérdida súbita del estado de alerta',
                    'immediate_apnea' => 'Apnea',
                    'immediate_no_pulse' => 'Ausencia de pulso',
                    'immediate_intubation' => 'Intubación de la vía aérea',
                    'immediate_angina' => 'Angor o equivalente anginoso',
                ];
            @endphp
            @foreach($immediates as $name => $label)
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" name="{{ $name }}"
                               id="{{ $name }}" value="1"
                               class="form-check-input immediate-check"
                               {{ old($name, $triage->$name ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $name }}">
                            {{ $label }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- TABLA A: 12 datos clínicos --}}
@php
    $tableA = [
        ['name' => 'trauma_score', 'label' => 'Traumatismo',
         'options' => [0 => 'Ausente', 5 => 'Menor', 10 => 'Moderado', 15 => 'Mayor']],
        ['name' => 'wound_score', 'label' => 'Herida',
         'options' => [0 => 'Ausente', 5 => 'Superficial', 10 => 'No penetrante', 15 => 'Extensa/Profunda']],
        ['name' => 'respiratory_difficulty_score', 'label' => 'Dificultad respiratoria',
         'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
        ['name' => 'cyanosis_score', 'label' => 'Cianosis',
         'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
        ['name' => 'paleness_score', 'label' => 'Palidez',
         'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
        ['name' => 'hemorrhage_score', 'label' => 'Hemorragia',
         'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
        ['name' => 'pain_score', 'label' => 'Dolor (escala visual análoga)',
         'options' => [0 => '0', 5 => '1 a 4', 10 => '5 a 8', 15 => '9 a 10']],
        ['name' => 'intoxication_score', 'label' => 'Intoxicación o autodaño',
         'options' => [0 => 'Ausente', 10 => 'Dudosa', 15 => 'Evidente']],
        ['name' => 'seizures_score', 'label' => 'Convulsiones',
         'options' => [0 => 'Ausente', 10 => 'Estado postictal', 15 => 'Presentes']],
        ['name' => 'glasgow_score', 'label' => 'Escala de coma de Glasgow',
         'options' => [0 => '15', 5 => '12 a 14', 10 => '8 a 11', 15 => '<8']],
        ['name' => 'dehydration_score', 'label' => 'Deshidratación',
         'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
        ['name' => 'psychosis_score', 'label' => 'Psicosis, agitación o violencia',
         'options' => [0 => 'Ausente', 15 => 'Presente']],
    ];
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-table me-1"></i>Calificaci&oacute;n A &mdash; Datos cl&iacute;nicos</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:30%;">Dato</th>
                        <th class="text-center" style="width:14%;">0</th>
                        <th class="text-center" style="width:14%;">5</th>
                        <th class="text-center" style="width:14%;">10</th>
                        <th class="text-center" style="width:14%;">15</th>
                        <th class="text-center" style="width:80px;">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableA as $row)
                        <tr>
                            <td><strong>{{ $row['label'] }}</strong></td>
                            @foreach([0, 5, 10, 15] as $points)
                                <td class="text-center">
                                    @if(array_key_exists($points, $row['options']))
                                        <label class="form-check-label d-block" style="cursor:pointer;">
                                            <input type="radio"
                                                   name="{{ $row['name'] }}"
                                                   value="{{ $points }}"
                                                   class="form-check-input score-a"
                                                   {{ (int) old($row['name'], $triage->{$row['name']} ?? 0) === $points ? 'checked' : '' }}>
                                            <small>{{ $row['options'][$points] }}</small>
                                        </label>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                <span class="badge bg-secondary" data-points-for="{{ $row['name'] }}">
                                    {{ (int) old($row['name'], $triage->{$row['name']} ?? 0) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end"><strong>Suma parcial A:</strong></td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6" id="sum-a">0</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- TABLA B: 5 parámetros --}}
@php
    $tableB = [
        ['name' => 'bp_score', 'label' => 'Tensión arterial',
         'cols' => [
             ['value' => 10, 'key' => 'low2',  'range' => '<70/50'],
             ['value' => 5,  'key' => 'low1',  'range' => '70/50-90/60'],
             ['value' => 0,  'key' => 'normal','range' => '91/61-120/80'],
             ['value' => 5,  'key' => 'high1', 'range' => '121/81-160/110'],
             ['value' => 10, 'key' => 'high2', 'range' => '>160/110'],
         ]],
        ['name' => 'hr_score', 'label' => 'Frecuencia Cardíaca',
         'cols' => [
             ['value' => 10, 'key' => 'low2',  'range' => '<40'],
             ['value' => 5,  'key' => 'low1',  'range' => '41-60'],
             ['value' => 0,  'key' => 'normal','range' => '61-100'],
             ['value' => 5,  'key' => 'high1', 'range' => '101-140'],
             ['value' => 10, 'key' => 'high2', 'range' => '>140'],
         ]],
        ['name' => 'rr_score', 'label' => 'Frecuencia respiratoria',
         'cols' => [
             ['value' => 10, 'key' => 'low2',  'range' => '<8'],
             ['value' => 5,  'key' => 'low1',  'range' => '9-12'],
             ['value' => 0,  'key' => 'normal','range' => '13-19'],
             ['value' => 5,  'key' => 'high1', 'range' => '20-25'],
             ['value' => 10, 'key' => 'high2', 'range' => '>25'],
         ]],
        ['name' => 'temp_score', 'label' => 'Temperatura',
         'cols' => [
             ['value' => 10, 'key' => 'low2',  'range' => '<34.5'],
             ['value' => 5,  'key' => 'low1',  'range' => '34.5-35.9'],
             ['value' => 0,  'key' => 'normal','range' => '36-37.1'],
             ['value' => 5,  'key' => 'high1', 'range' => '37.1-39'],
             ['value' => 10, 'key' => 'high2', 'range' => '>39'],
         ]],
        ['name' => 'glucose_score', 'label' => 'Glucemia capilar',
         'cols' => [
             ['value' => 10, 'key' => 'low2',  'range' => '<40'],
             ['value' => 5,  'key' => 'low1',  'range' => '40-60'],
             ['value' => 0,  'key' => 'normal','range' => '61-130'],
             ['value' => 5,  'key' => 'high1', 'range' => '131-400'],
             ['value' => 10, 'key' => 'high2', 'range' => '>400'],
         ]],
    ];
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-table me-1"></i>Calificaci&oacute;n B &mdash; Par&aacute;metros fisiol&oacute;gicos</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:25%;">Par&aacute;metro</th>
                        <th class="text-center" style="width:12%;">10</th>
                        <th class="text-center" style="width:12%;">5</th>
                        <th class="text-center" style="width:12%;">0</th>
                        <th class="text-center" style="width:12%;">5</th>
                        <th class="text-center" style="width:12%;">10</th>
                        <th class="text-center" style="width:80px;">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableB as $row)
                        @php
                            $currentVal = (int) old($row['name'], $triage->{$row['name']} ?? 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $row['label'] }}</strong></td>
                            @foreach($row['cols'] as $col)
                                <td class="text-center">
                                    <label class="form-check-label d-block" style="cursor:pointer;">
                                        <input type="radio"
                                               name="{{ $row['name'] }}"
                                               value="{{ $col['value'] }}"
                                               data-col-key="{{ $col['key'] }}"
                                               class="form-check-input score-b"
                                               {{ $col['key'] === 'normal' && $currentVal === 0 ? 'checked' : '' }}
                                               @if($currentVal === $col['value'] && $col['key'] !== 'normal' && $currentVal !== 0)
                                                   {{-- Para edición: solo podemos saber el valor, no la dirección.
                                                        Sin hidden extra, al recargar se pierde la dirección.
                                                        Se maneja por defecto la primera coincidencia. --}}
                                               @endif
                                        >
                                        <small>{{ $col['range'] }}</small>
                                    </label>
                                </td>
                            @endforeach
                            <td class="text-center">
                                <span class="badge bg-secondary" data-points-for="{{ $row['name'] }}">
                                    {{ $currentVal }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end"><strong>Suma parcial B:</strong></td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6" id="sum-b">0</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Clasificación final --}}
<div class="card mb-3" id="classification-card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-speedometer2 me-1"></i>Clasificaci&oacute;n final</h6>
    </div>
    <div class="card-body">
        <div class="row align-items-center text-center">
            <div class="col-md-3">
                <small class="text-muted d-block">Puntaje total (A+B)</small>
                <span class="badge bg-dark fs-3" id="total-score">0</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Color</small>
                <span class="badge bg-primary fs-3" id="color-badge">Azul</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Decisi&oacute;n</small>
                <span class="fs-5" id="decision-text">Sin urgencia</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Sitio sugerido</small>
                <span class="fs-6" id="site-text">Consultorio</span>
            </div>
        </div>
        <div id="immediate-alert-banner" class="alert alert-danger mt-3 mb-0 d-none">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>ATENCI&Oacute;N INMEDIATA REQUERIDA</strong> &mdash; El paciente
            se clasifica autom&aacute;ticamente como ROJO por presentar uno o
            m&aacute;s criterios de alerta.
        </div>
    </div>
</div>

<style>
    .bg-orange { background-color: #fd7e14 !important; color: #000 !important; }
</style>
