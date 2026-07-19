@extends('pdfs.layouts.base')

@section('document-title', '')
@section('document-subtitle', '')

@section('content')
<style>
    @page { margin: 90px 30px 50px 30px; }
    .doc-title { display:none; }
    .section-header {
        background-color:#E91E63; color:white;
        padding:3px 8px; font-size:9pt; font-weight:bold;
        margin-bottom:4px; margin-top:8px;
    }
    .sub-header {
        background-color:#fce4ec; color:#880e4f;
        padding:2px 6px; font-size:8.5pt; font-weight:bold;
        margin-bottom:3px; margin-top:5px;
    }
    .field-row { font-size:8.5pt; line-height:1.4; margin-bottom:4px; }
    .field-row strong { color:#444; }
    .narrative { font-size:9pt; line-height:1.45; white-space:pre-wrap; margin-bottom:6px; }
</style>

@php
    $age = $patient->birth_date
        ? \Carbon\Carbon::parse($patient->birth_date)->age
        : null;
    $genderLabel = (strtoupper($patient->gender ?? '') === 'M') ? 'Masculino' : 'Femenino';
@endphp

{{-- Encabezado CMG --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:6px;">
    <tr>
        <td style="width:70px; vertical-align:middle;">
            @php
                $logoPath = public_path('logos/CMG.png');
                $logoData = is_file($logoPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                    : null;
            @endphp
            @if($logoData)
                <img src="{{ $logoData }}" style="max-height:44px; max-width:65px;" alt="CMG">
            @endif
        </td>
        <td style="vertical-align:middle;">
            <div style="background-color:#E91E63; color:white; padding:5px 12px;
                        font-weight:bold; font-size:11pt; text-align:center; letter-spacing:.4px;">
                NOTA DE ANESTESIA
            </div>
            <div style="text-align:right; font-size:7pt; color:#666; margin-top:3px;">
                Privada Solar #3 Zona Centro Acámbaro, GTO. | C.P. 38600 | Tel. 01 (417) 172 04 30
            </div>
        </td>
    </tr>
</table>

{{-- Datos del paciente --}}
<table style="width:100%; border:1px solid #333; border-collapse:collapse;
              margin-bottom:8px; font-size:9pt;">
    <tr>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; width:55%;">
            <strong>Paciente:</strong> {{ $patient->fullName() }}
        </td>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; border-left:1px solid #ddd;">
            <strong>Edad:</strong> {{ $age ? $age . ' años' : '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
            <strong>F. Nacimiento:</strong>
            {{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') : '—' }}
        </td>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; border-left:1px solid #ddd;">
            <strong>Género:</strong> {{ $genderLabel }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
            <strong>F. Ingreso:</strong>
            {{ \Carbon\Carbon::parse($stay->admission_date)->format('d/m/Y') }}
        </td>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; border-left:1px solid #ddd;">
            <strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px;">
            <strong>Nº Expediente:</strong> {{ $patient->id }}
        </td>
        <td style="padding:3px 6px; border-left:1px solid #ddd;">
            <strong>Anestesiólogo:</strong> Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
        </td>
    </tr>
</table>

{{-- ====================================================== --}}
{{-- SECCIÓN 1: VALORACIÓN PREANESTÉSICA --}}
{{-- ====================================================== --}}
<div class="section-header">1. VALORACIÓN PREANESTÉSICA</div>

<table style="width:100%; font-size:8.5pt; border-collapse:collapse; margin-bottom:5px;">
    <tr>
        <td style="width:33%; padding:2px 4px;"><strong>Tipo:</strong> {{ $note->surgery_urgency ? ucfirst($note->surgery_urgency) : '—' }}</td>
        <td style="padding:2px 4px;"><strong>Dx prequirúrgico:</strong> {{ $note->preop_diagnosis ?? '—' }}</td>
    </tr>
    @if($note->planned_surgery)
    <tr>
        <td colspan="2" style="padding:2px 4px;"><strong>Cirugía programada:</strong> {{ $note->planned_surgery }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding:2px 4px;"><strong>ASA:</strong> {{ $note->asa_status ?? '—' }}</td>
        <td style="padding:2px 4px;"><strong>Peso:</strong> {{ $note->weight_kg ?? '—' }} kg &nbsp; <strong>Talla:</strong> {{ $note->height_m ?? '—' }} m</td>
    </tr>
    <tr>
        <td style="padding:2px 4px;"><strong>Conciencia:</strong> {{ $note->consciousness_state ? ucfirst($note->consciousness_state) : '—' }}</td>
        <td style="padding:2px 4px;"><strong>TA:</strong> {{ $note->exam_ta ?? '—' }} &nbsp; <strong>FC:</strong> {{ $note->exam_fc ?? '—' }} &nbsp; <strong>FR:</strong> {{ $note->exam_fr ?? '—' }} &nbsp; <strong>Temp:</strong> {{ $note->exam_temp ?? '—' }}</td>
    </tr>
</table>

{{-- Antecedentes positivos --}}
@php
    $positiveAntecedents = [];
    foreach($antecedents as $key => $label) {
        if (!empty($note->antecedents[$key]['has_condition'])) {
            $evo = $note->antecedents[$key]['evolution_time'] ?? null;
            $positiveAntecedents[] = $label . ($evo ? ' (' . $evo . ')' : '');
        }
    }
@endphp
@if($positiveAntecedents)
    <div class="sub-header">Antecedentes positivos</div>
    <p style="font-size:8.5pt; margin:0 0 4px 0;">{{ implode(' · ', $positiveAntecedents) }}</p>
@endif

@if($note->current_medications)
    <div class="field-row"><strong>Medicamentos actuales:</strong> {{ $note->current_medications }}</div>
@endif
@if($note->previous_anesthesias)
    <div class="field-row"><strong>Anestesias previas:</strong> {{ $note->previous_anesthesias }}</div>
@endif
@if($note->current_illness)
    <div class="sub-header">Padecimiento actual</div>
    <div class="narrative">{{ $note->current_illness }}</div>
@endif

{{-- Exploración --}}
@php
    $examFields = array_filter([
        'head_neck_exam' => 'Cabeza/cuello', 'airway_exam' => 'Vía aérea',
        'cardiopulmonary_exam' => 'Cardiopulmonar', 'abdomen_exam' => 'Abdomen',
        'spine_exam' => 'Columna', 'extremities_exam' => 'Extremidades',
    ], fn($k) => !empty(trim($note->{$k} ?? '')), ARRAY_FILTER_USE_KEY);
@endphp
@if($examFields)
    <div class="sub-header">Exploración física</div>
    @foreach($examFields as $field => $label)
        <div class="field-row"><strong>{{ $label }}:</strong> {{ $note->{$field} }}</div>
    @endforeach
@endif

{{-- Laboratorio --}}
@php
    $labVals = array_filter([
        'Hb' => $note->lab_hb, 'Hto' => $note->lab_hto, 'TP' => $note->lab_tp,
        'TPT' => $note->lab_tpt, 'Grupo/Rh' => $note->lab_blood_type_rh,
        'Glucosa' => $note->lab_glucose, 'Urea' => $note->lab_urea, 'Creatinina' => $note->lab_creatinine,
    ]);
@endphp
@if($labVals)
    <div class="sub-header">Laboratorio</div>
    <div style="font-size:8.5pt;">
        @foreach($labVals as $lbl => $val)
            <strong>{{ $lbl }}:</strong> {{ $val }} &nbsp;&nbsp;
        @endforeach
    </div>
@endif
@if($note->anesthetic_plan)
    <div class="sub-header">Plan anestésico</div>
    <div class="narrative">{{ $note->anesthetic_plan }}</div>
@endif
@if($note->preanesthetic_indications)
    <div class="field-row"><strong>Indicaciones preanestésicas:</strong> {{ $note->preanesthetic_indications }}</div>
@endif

{{-- ====================================================== --}}
{{-- SECCIÓN 2: REGISTRO ANESTÉSICO --}}
{{-- ====================================================== --}}
<div style="page-break-before: always;"></div>
<div class="section-header">2. REGISTRO ANESTÉSICO</div>

@if($note->postop_diagnosis)
    <div class="field-row"><strong>Dx postoperatorio:</strong> {{ $note->postop_diagnosis }}</div>
@endif
@if($note->performed_surgery)
    <div class="field-row"><strong>Cirugía realizada:</strong> {{ $note->performed_surgery }}</div>
@endif

{{-- Equipo quirúrgico --}}
<div style="background-color:#f5f5f5; padding:4px 8px; margin:5px 0;
            font-size:8.5pt; border-left:3px solid #E91E63;">
    <strong>Equipo quirúrgico:</strong>
    Cirujano: {{ $note->orSurgeonName() }} &nbsp;|&nbsp;
    Ayudante: {{ $note->orAssistantName() }}
</div>

{{-- Tiempos --}}
@php
    $timesData = array_filter([
        'Inicio anestesia' => $note->anesthesia_start?->format('H:i'),
        'Fin anestesia'    => $note->anesthesia_end?->format('H:i'),
        'Inicio Qx'        => $note->surgery_start?->format('H:i'),
        'Fin Qx'           => $note->surgery_end?->format('H:i'),
        'Tiempo total'     => $note->anesthetic_time_total,
        'Dosis total'      => $note->total_dose,
    ]);
@endphp
@if($timesData)
    <div style="font-size:8.5pt; margin-bottom:4px;">
        @foreach($timesData as $lbl => $val)
            <strong>{{ $lbl }}:</strong> {{ $val }} &nbsp;&nbsp;
        @endforeach
    </div>
@endif

{{-- Balance de líquidos --}}
@php
    $flIn  = array_filter(['Hartmann' => $note->fluids_in_hartmann, 'Glucosa' => $note->fluids_in_glucose, 'NaCl' => $note->fluids_in_nacl]);
    $flOut = array_filter(['Diuresis' => $note->fluids_out_diuresis, 'Sangrado' => $note->fluids_out_bleeding, 'Pérd. insensibles' => $note->fluids_out_insensible_losses]);
@endphp
@if($flIn || $flOut)
    <div style="font-size:8.5pt; margin-bottom:4px;">
        @if($flIn) <strong>Ingresos:</strong> @foreach($flIn as $l => $v) {{ $l }}: {{ $v }}ml &nbsp; @endforeach @endif
        @if($flOut) &nbsp;|&nbsp; <strong>Egresos:</strong> @foreach($flOut as $l => $v) {{ $l }}: {{ $v }}ml &nbsp; @endforeach @endif
    </div>
@endif

{{-- Monitoreo e intubación --}}
@php
    $monitors = array_filter(['ECG continuo' => $note->continuous_ecg, 'Oximetría de pulso' => $note->pulse_oximetry, 'Capnografía' => $note->capnography]);
@endphp
@if($note->intubation_blade || $note->intubation_cannula || $monitors)
    <div style="font-size:8.5pt; margin-bottom:4px;">
        @if($note->intubation_blade) <strong>Hoja laringo.:</strong> {{ $note->intubation_blade }} &nbsp; @endif
        @if($note->intubation_cannula) <strong>Cánula:</strong> {{ $note->intubation_cannula }} &nbsp; @endif
        @if($note->intubation_technical_difficulty) <strong>Dificultad técnica</strong> @if($note->intubation_difficulty_detail): {{ $note->intubation_difficulty_detail }}@endif &nbsp; @endif
        @foreach(array_keys($monitors) as $m) {{ $m }} · @endforeach
    </div>
@endif

{{-- Anestesia regional --}}
@if($note->regional_anesthesia_type)
    <div style="font-size:8.5pt; margin-bottom:4px;">
        <strong>Anestesia regional:</strong>
        {{ $note->regional_anesthesia_type }}
        @if($note->regional_needle) — Aguja: {{ $note->regional_needle }}@endif
        @if($note->regional_puncture_level) — Nivel: {{ $note->regional_puncture_level }}@endif
        @if($note->regional_catheter) — Con catéter @endif
    </div>
@endif

{{-- Aldrete OR exit --}}
@if($note->aldrete_or_exit)
    <div class="sub-header">Aldrete al salir de quirófano — Total: {{ $note->aldreteTotal($note->aldrete_or_exit) ?? '—' }}/10</div>
    <table style="width:100%; font-size:7.5pt; border-collapse:collapse; margin-bottom:5px;">
        <tr>
            @foreach($aldreteScale as $key => $criterion)
                @if(isset($note->aldrete_or_exit[$key]))
                    <td style="border:1px solid #ddd; padding:2px 3px; text-align:center; width:20%;">
                        <div>{{ $criterion['label'] }}</div>
                        <div><strong>{{ $note->aldrete_or_exit[$key] }}</strong></div>
                    </td>
                @endif
            @endforeach
        </tr>
    </table>
@endif

{{-- Signos vitales transoperatorios --}}
<div style="page-break-inside: avoid;">
@if($note->vitalReadings->isNotEmpty())
    <div class="sub-header">Signos vitales transoperatorios</div>

    @if($chartImage)
        <img src="{{ $chartImage }}" style="width:100%; max-width:700px; display:block; margin-bottom:4px;">
    @else
        <p style="color:#c00; font-size:8pt;">No fue posible generar la gráfica de signos vitales.</p>
    @endif

    <table style="width:100%; font-size:7.5pt; border-collapse:collapse; margin-bottom:6px;">
        <thead>
            <tr style="background:#f5f5f5;">
                <th style="border:1px solid #ddd; padding:2px;">Hora</th>
                <th style="border:1px solid #ddd; padding:2px;">TA</th>
                <th style="border:1px solid #ddd; padding:2px;">FC</th>
                <th style="border:1px solid #ddd; padding:2px;">FR</th>
                <th style="border:1px solid #ddd; padding:2px;">Temp</th>
                <th style="border:1px solid #ddd; padding:2px;">SpO2</th>
                <th style="border:1px solid #ddd; padding:2px;">Evento</th>
                <th style="border:1px solid #ddd; padding:2px;">Hartmann</th>
                <th style="border:1px solid #ddd; padding:2px;">Glucosa</th>
                <th style="border:1px solid #ddd; padding:2px;">NaCl</th>
            </tr>
        </thead>
        <tbody>
            @foreach($note->vitalReadings as $r)
                <tr>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ \Illuminate\Support\Str::substr($r->reading_time, 0, 5) }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->ta_sys ?? '—' }}/{{ $r->ta_dia ?? '—' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->fc ?? '—' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->fr ?? '—' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->temp ?? '—' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->spo2 !== null ? $r->spo2 . '%' : '—' }}</td>
                    <td style="border:1px solid #ddd; padding:2px;">{{ $r->event_marker ?? '' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->hartmann_ml ?? '' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->glucose_ml ?? '' }}</td>
                    <td style="border:1px solid #ddd; padding:2px; text-align:center;">{{ $r->nacl_ml ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p style="color:#888; font-size:8pt;">Sin lecturas de signos vitales registradas.</p>
@endif
</div>

{{-- ====================================================== --}}
{{-- SECCIÓN 3: NOTA POST ANESTÉSICA --}}
{{-- ====================================================== --}}
<div style="page-break-before: always;"></div>
<div class="section-header">3. NOTA POST ANESTÉSICA</div>

@if($note->anesthetic_technique_and_drugs)
    <div class="sub-header">Técnica anestésica y fármacos</div>
    <div class="narrative">{{ $note->anesthetic_technique_and_drugs }}</div>
@endif
@if($note->blood_fluids_administered)
    <div class="field-row"><strong>Hemoderivados y líquidos administrados:</strong> {{ $note->blood_fluids_administered }}</div>
@endif
@if($note->incidents_or_accidents)
    <div class="field-row" style="color:#c62828;"><strong>INCIDENTES/ACCIDENTES REPORTADOS</strong></div>
@endif
@if($note->management_plan)
    <div class="field-row"><strong>Plan de manejo:</strong> {{ $note->management_plan }}</div>
@endif

{{-- Ingreso UCPA --}}
@php
    $admVitals = array_filter([
        'TA' => $note->ucpa_admission_ta, 'FC' => $note->ucpa_admission_fc,
        'FR' => $note->ucpa_admission_fr, 'SpO2' => $note->ucpa_admission_spo2,
    ]);
@endphp
@if($admVitals || $note->aldrete_ucpa_admission)
    <div class="sub-header">Ingreso UCPA
        @if($note->aldrete_ucpa_admission) — Aldrete: {{ $note->aldreteTotal($note->aldrete_ucpa_admission) ?? '—' }}/10 @endif
    </div>
    @if($admVitals)
        <div style="font-size:8.5pt; margin-bottom:3px;">
            @foreach($admVitals as $l => $v)<strong>{{ $l }}:</strong> {{ $v }} &nbsp; @endforeach
        </div>
    @endif
    @if($note->aldrete_ucpa_admission)
        <table style="width:100%; font-size:7.5pt; border-collapse:collapse; margin-bottom:4px;">
            <tr>
                @foreach($aldreteScale as $key => $criterion)
                    @if(isset($note->aldrete_ucpa_admission[$key]))
                        <td style="border:1px solid #ddd; padding:2px 3px; text-align:center; width:20%;">
                            <div>{{ $criterion['label'] }}</div><div><strong>{{ $note->aldrete_ucpa_admission[$key] }}</strong></div>
                        </td>
                    @endif
                @endforeach
            </tr>
        </table>
    @endif
@endif

@if($note->evolution_and_ucpa_discharge)
    <div class="sub-header">Evolución y alta UCPA
        @if($note->aldrete_ucpa_discharge) — Aldrete alta: {{ $note->aldreteTotal($note->aldrete_ucpa_discharge) ?? '—' }}/10 @endif
    </div>
    <div class="narrative">{{ $note->evolution_and_ucpa_discharge }}</div>
    @php
        $disVitals = array_filter(['TA' => $note->ucpa_discharge_ta, 'FC' => $note->ucpa_discharge_fc, 'FR' => $note->ucpa_discharge_fr, 'SpO2' => $note->ucpa_discharge_spo2]);
    @endphp
    @if($disVitals)
        <div style="font-size:8.5pt; margin-bottom:3px;">
            @foreach($disVitals as $l => $v)<strong>{{ $l }}:</strong> {{ $v }} &nbsp; @endforeach
        </div>
    @endif
@endif

@if($note->postop_pain_control)
    <div class="field-row"><strong>Control de dolor postoperatorio:</strong> {{ $note->postop_pain_control }}</div>
@endif

{{-- Alta de anestesiología --}}
@php
    $disData = array_filter([
        'TA' => $note->discharge_ta, 'Pulso' => $note->discharge_pulse,
        'Resp.' => $note->discharge_resp,
        'Conciencia' => $note->discharge_consciousness ? ucfirst($note->discharge_consciousness) : null,
        'Diuresis' => $note->discharge_diuresis, 'Dolor' => $note->discharge_pain,
    ]);
    $disSymptoms = array_keys(array_filter([
        'Náusea' => $note->discharge_nausea, 'Vómito' => $note->discharge_vomiting,
        'Cefálea' => $note->discharge_headache, 'Deambula' => $note->discharge_ambulation,
    ]));
@endphp
@if($disData || $disSymptoms || $note->discharge_evolution)
    <div class="sub-header">Alta de anestesiología</div>
    @if($disData)
        <div style="font-size:8.5pt; margin-bottom:3px;">
            @foreach($disData as $l => $v)<strong>{{ $l }}:</strong> {{ $v }} &nbsp; @endforeach
            @if($disSymptoms) &nbsp; {{ implode(' · ', $disSymptoms) }} @endif
        </div>
    @endif
    @if($note->discharge_evolution)
        <div class="narrative">{{ $note->discharge_evolution }}</div>
    @endif
    @if($note->discharge_indications)
        <div class="field-row"><strong>Indicaciones al alta:</strong> {{ $note->discharge_indications }}</div>
    @endif
@endif

{{-- Firma --}}
<div style="margin-top:40px; text-align:center; font-size:9.5pt; line-height:1.6;">
    <div style="display:inline-block; border-top:1px solid #333; padding-top:4px; min-width:220px;">
        {!! nl2br(e($note->effectiveSignatureBlock())) !!}
    </div>
</div>

@endsection
