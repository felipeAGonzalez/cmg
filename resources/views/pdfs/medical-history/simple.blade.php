@extends('pdfs.layouts.base')

@section('document-title', 'Historia Clínica')

@section('content')
@php
    use Illuminate\Support\Str;
    $nonPath  = $history->simple_non_pathological_checks ?? [];
    $pathChks = $history->simple_pathological_checks ?? [];
    $gyneco   = $history->simple_gyneco_history ?? [];
    $gynecoV  = $history->simple_gyneco_vaccines ?? [];
    $ros      = collect($history->simple_review_of_systems ?? [])->filter(fn($v) => !empty(trim($v ?? '')));
    $painSgns = $history->simple_pain_associated_signs ?? [];
    $examSys  = $history->simple_exam_by_system ?? [];
@endphp

{{-- ── Encabezado ── --}}
<div style="background-color:#E91E63; color:white; padding:5px 10px; font-weight:bold; text-align:center; font-size:9pt; margin-bottom:8px;">
    HISTORIA CLÍNICA
</div>

{{-- ── Datos del paciente ── --}}
<table style="width:100%; border-collapse:collapse; font-size:7.5pt; margin-bottom:8px;">
    <tr>
        <td style="width:50%; padding:2px 4px;"><strong>Paciente:</strong> {{ $patient->fullName() }}</td>
        <td style="width:25%; padding:2px 4px;"><strong>Edad:</strong> {{ $patient->birth_date ? $patient->birth_date->age . ' años' : '—' }}</td>
        <td style="width:25%; padding:2px 4px;"><strong>Sexo:</strong> {{ $patient->gender === 'M' ? 'Masculino' : ($patient->gender === 'F' ? 'Femenino' : '—') }}</td>
    </tr>
    <tr>
        <td style="padding:2px 4px;"><strong>F. nacimiento:</strong> {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '—' }}</td>
        <td style="padding:2px 4px;"><strong>Cuarto:</strong> {{ $stay->room->number ?? '—' }}</td>
        <td style="padding:2px 4px;"><strong>Ingreso:</strong> {{ $stay->admission_date ? $stay->admission_date->format('d/m/Y') : '—' }}</td>
    </tr>
    @if($history->simple_interrogation_type)
    <tr>
        <td colspan="3" style="padding:2px 4px;"><strong>Interrogatorio:</strong> {{ ucfirst($history->simple_interrogation_type) }}</td>
    </tr>
    @endif
</table>

{{-- ── Antecedentes heredofamiliares ── --}}
@if($history->simple_heredo_father || $history->simple_heredo_mother || $history->simple_heredo_other)
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">ANTECEDENTES HEREDOFAMILIARES</div>
    <table style="width:100%; border-collapse:collapse; font-size:7pt;">
        @if($history->simple_heredo_father)
        <tr><td style="width:80px; padding:2px 4px; font-weight:bold;">Padre:</td><td style="padding:2px 4px;">{{ $history->simple_heredo_father }}</td></tr>
        @endif
        @if($history->simple_heredo_mother)
        <tr><td style="padding:2px 4px; font-weight:bold;">Madre:</td><td style="padding:2px 4px;">{{ $history->simple_heredo_mother }}</td></tr>
        @endif
        @if($history->simple_heredo_other)
        <tr><td style="padding:2px 4px; font-weight:bold;">Otros:</td><td style="padding:2px 4px;">{{ $history->simple_heredo_other }}</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Antecedentes no patológicos ── --}}
@php
    $hasNonPath = $history->simple_origin || $history->simple_occupation || $history->simple_education
        || $history->simple_blood_type_rh || $history->simple_hygiene || $history->simple_diet
        || collect($nonPath)->contains(fn($v) => !empty($v['has_condition'] ?? false));
@endphp
@if($hasNonPath)
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">ANTECEDENTES PERSONALES NO PATOLÓGICOS</div>
    <div style="font-size:7pt; padding:3px 4px;">
        @if($history->simple_origin)<span><strong>Originario:</strong> {{ $history->simple_origin }}</span> &nbsp; @endif
        @if($history->simple_resident_of)<span><strong>Residente:</strong> {{ $history->simple_resident_of }}</span> &nbsp; @endif
        @if($history->simple_occupation)<span><strong>Ocupación:</strong> {{ $history->simple_occupation }}</span> &nbsp; @endif
        @if($history->simple_education)<span><strong>Escolaridad:</strong> {{ $history->simple_education }}</span> &nbsp; @endif
        @if($history->simple_marital_status)<span><strong>E. civil:</strong> {{ ucfirst($history->simple_marital_status) }}{{ $history->simple_marital_status_other ? ' (' . $history->simple_marital_status_other . ')' : '' }}</span> &nbsp; @endif
        @if($history->simple_blood_type_rh)<span><strong>Grupo/Rh:</strong> {{ $history->simple_blood_type_rh }}</span> &nbsp; @endif
        @if($history->simple_diet)<span><strong>Alimentación:</strong> {{ $history->simple_diet }}</span> &nbsp; @endif
        @if($history->simple_religion)<span><strong>Religión:</strong> {{ $history->simple_religion }}</span>@endif
    </div>
    @if($history->simple_hygiene)
    <div style="font-size:7pt; padding:2px 4px;"><strong>Higiénicos:</strong> {{ $history->simple_hygiene }}</div>
    @endif
    @php $npPositive = collect($nonPath)->filter(fn($v) => !empty($v['has_condition'] ?? false)); @endphp
    @if($npPositive->isNotEmpty())
    <table style="width:100%; border-collapse:collapse; font-size:7pt; margin-top:3px;">
        @foreach($npPositive as $npKey => $npItem)
        <tr>
            <td style="width:160px; padding:1px 4px; font-weight:bold;">{{ $simpleConfigs['nonPathologicalChecks'][$npKey] ?? $npKey }}</td>
            <td style="padding:1px 4px; color:#c00; font-weight:bold;">Sí</td>
            <td style="padding:1px 4px;">{{ $npItem['detail'] ?? '' }}</td>
        </tr>
        @endforeach
    </table>
    @endif
    @if($history->simple_non_pathological_other)
    <div style="font-size:7pt; padding:2px 4px;"><strong>Otros:</strong> {{ $history->simple_non_pathological_other }}</div>
    @endif
</div>
@endif

{{-- ── Antecedentes patológicos ── --}}
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">ANTECEDENTES PERSONALES PATOLÓGICOS</div>
    <table style="width:100%; border-collapse:collapse; font-size:7pt; margin-top:2px;">
        <thead>
            <tr style="background:#f5f5f5;">
                <th style="padding:2px 4px; border:1px solid #ddd; text-align:left;">Antecedente</th>
                <th style="padding:2px 4px; border:1px solid #ddd; width:50px; text-align:center;">Sí/No</th>
                <th style="padding:2px 4px; border:1px solid #ddd; text-align:left;">Detalle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($simpleConfigs['pathologicalChecks'] as $pKey => $pLabel)
                @php $pItem = $pathChks[$pKey] ?? null; $pHas = !empty($pItem['has_condition'] ?? false); @endphp
                <tr>
                    <td style="padding:2px 4px; border:1px solid #ddd;">{{ $pLabel }}</td>
                    <td style="padding:2px 4px; border:1px solid #ddd; text-align:center; {{ $pHas ? 'color:#c00; font-weight:bold;' : '' }}">{{ $pHas ? 'Sí' : 'No' }}</td>
                    <td style="padding:2px 4px; border:1px solid #ddd;">{{ $pItem['detail'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($history->simple_pathological_other)
    <div style="font-size:7pt; padding:2px 4px; margin-top:2px;"><strong>Otros:</strong> {{ $history->simple_pathological_other }}</div>
    @endif
    @if($history->simple_anesthetics_history)
    <div style="font-size:7pt; padding:2px 4px;"><strong>Antecedentes anestésicos:</strong> {{ $history->simple_anesthetics_history }}</div>
    @endif
</div>

{{-- ── Antecedentes gineco-obstétricos ── --}}
@php
    $gynecoFields = array_filter($gyneco, fn($v) => !empty(trim($v ?? '')));
    $gynecoVPos   = array_filter($gynecoV);
@endphp
@if(!empty($gynecoFields) || !empty($gynecoVPos))
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">ANTECEDENTES GINECO-OBSTÉTRICOS</div>
    <div style="font-size:7pt; padding:3px 4px;">
        @if(!empty($gyneco['menarche']))<span><strong>Menarca:</strong> {{ $gyneco['menarche'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['rhythm']))<span><strong>Ritmo:</strong> {{ $gyneco['rhythm'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['lmp']))<span><strong>FUM:</strong> {{ $gyneco['lmp'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['ivsa']))<span><strong>IVSA:</strong> {{ $gyneco['ivsa'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['partners']))<span><strong>Parejas:</strong> {{ $gyneco['partners'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['sti']))<span><strong>ITS:</strong> {{ $gyneco['sti'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['contraception']))<span><strong>Planificación:</strong> {{ $gyneco['contraception'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['hrt']))<span><strong>TRH:</strong> {{ $gyneco['hrt'] }}</span>@endif
    </div>
    @php $obsFields = array_filter(array_intersect_key($gyneco, array_flip(['gravida','para','cesarean','abortion','ectopic']))); @endphp
    @if(!empty($obsFields))
    <div style="font-size:7pt; padding:2px 4px;">
        @if(!empty($gyneco['gravida']))<span><strong>G:</strong> {{ $gyneco['gravida'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['para']))<span><strong>P:</strong> {{ $gyneco['para'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['cesarean']))<span><strong>C:</strong> {{ $gyneco['cesarean'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['abortion']))<span><strong>A:</strong> {{ $gyneco['abortion'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['ectopic']))<span><strong>E:</strong> {{ $gyneco['ectopic'] }}</span>@endif
    </div>
    @endif
    @if(!empty($gyneco['pap_smear']) || !empty($gyneco['mammography']))
    <div style="font-size:7pt; padding:2px 4px;">
        @if(!empty($gyneco['pap_smear']))<span><strong>Citología:</strong> {{ $gyneco['pap_smear'] }}</span> &nbsp; @endif
        @if(!empty($gyneco['mammography']))<span><strong>Mastografía:</strong> {{ $gyneco['mammography'] }}</span>@endif
    </div>
    @endif
    @if(!empty($gyneco['alterations']))
    <div style="font-size:7pt; padding:2px 4px;"><strong>Alteraciones:</strong> {{ $gyneco['alterations'] }}</div>
    @endif
    @if(!empty($gynecoVPos))
    <div style="font-size:7pt; padding:2px 4px;">
        <strong>Vacunas:</strong>
        {{ collect($gynecoVPos)->keys()->map(fn($k) => $simpleConfigs['gynecoVaccines'][$k] ?? $k)->implode(', ') }}
    </div>
    @endif
</div>
@endif

{{-- ── Padecimiento actual ── --}}
@if($history->simple_current_illness)
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">PADECIMIENTO ACTUAL</div>
    <div style="font-size:7.5pt; padding:4px 6px;">{{ $history->simple_current_illness }}</div>
</div>
@endif

{{-- ── Revisión por aparatos y sistemas ── --}}
@if($ros->isNotEmpty())
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">REVISIÓN POR APARATOS Y SISTEMAS</div>
    <table style="width:100%; border-collapse:collapse; font-size:7pt; margin-top:2px;">
        @foreach($ros as $rKey => $rVal)
        <tr>
            <td style="width:160px; padding:2px 4px; border:1px solid #eee; font-weight:bold;">{{ $simpleConfigs['reviewOfSystems'][$rKey] ?? $rKey }}</td>
            <td style="padding:2px 4px; border:1px solid #eee;">{{ $rVal }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ── Valoración de dolor ── --}}
@if($history->painScaleSummary() || $history->simple_pain_type || $history->simple_pain_region)
<div style="margin-bottom:6px; background:#f5f5f5; padding:5px 8px; border-left:3px solid #E91E63;">
    <strong style="font-size:7pt;">VALORACIÓN DE DOLOR</strong>
    <div style="font-size:7.5pt; margin-top:2px;">
        @if($history->painScaleSummary())<span>{{ $history->painScaleSummary() }}</span>@endif
        @if($history->simple_pain_type) &nbsp;·&nbsp; <strong>Tipo:</strong> {{ ucfirst($history->simple_pain_type) }}@endif
        @if($history->simple_pain_region) &nbsp;·&nbsp; <strong>Región:</strong> {{ $history->simple_pain_region }}@endif
        @if($history->simple_pain_duration) &nbsp;·&nbsp; {{ ucfirst($history->simple_pain_duration) }}@endif
    </div>
    @php $signPos = collect($painSgns)->filter(); @endphp
    @if($signPos->isNotEmpty())
    <div style="font-size:7pt; margin-top:2px;">
        <strong>Signos asociados:</strong>
        {{ $signPos->keys()->map(fn($k) => $simpleConfigs['painSigns'][$k] ?? $k)->implode(', ') }}
    </div>
    @endif
    @if($history->simple_pain_associated_factors)
    <div style="font-size:7pt; margin-top:2px;"><strong>Factores asociados:</strong> {{ $history->simple_pain_associated_factors }}</div>
    @endif
</div>
@endif

{{-- ── Exploración física ── --}}
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">EXPLORACIÓN FÍSICA</div>
    <div style="font-size:7.5pt; padding:3px 6px;">
        <strong>Signos vitales:</strong>
        TA: {{ $history->simple_exam_ta ?? '—' }} &nbsp;|&nbsp;
        Pulso: {{ $history->simple_exam_pulse ?? '—' }} &nbsp;|&nbsp;
        FC: {{ $history->simple_exam_fc ?? '—' }} &nbsp;|&nbsp;
        FR: {{ $history->simple_exam_fr ?? '—' }} &nbsp;|&nbsp;
        Temp: {{ $history->simple_exam_temp ?? '—' }}
    </div>
    @php $examPos = collect($examSys)->filter(fn($v) => !empty(trim($v ?? ''))); @endphp
    @if($examPos->isNotEmpty())
    <table style="width:100%; border-collapse:collapse; font-size:7pt; margin-top:2px;">
        @foreach($examPos as $eKey => $eVal)
        <tr>
            <td style="width:160px; padding:2px 4px; border:1px solid #eee; font-weight:bold;">{{ $simpleConfigs['examBySystem'][$eKey] ?? $eKey }}</td>
            <td style="padding:2px 4px; border:1px solid #eee;">{{ $eVal }}</td>
        </tr>
        @endforeach
    </table>
    @endif
</div>

{{-- ── Estudios de laboratorio y gabinete ── --}}
@if($history->simple_lab_studies)
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">ESTUDIOS DE LABORATORIO, GABINETE Y OTROS</div>
    <div style="font-size:7.5pt; padding:4px 6px;">{{ $history->simple_lab_studies }}</div>
</div>
@endif

{{-- ── Diagnóstico ── --}}
@if($history->simple_diagnosis)
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">DIAGNÓSTICO</div>
    <div style="font-size:7.5pt; padding:4px 6px;">{{ $history->simple_diagnosis }}</div>
</div>
@endif

{{-- ── Terapéutica ── --}}
@if($history->simple_therapeutics)
<div style="margin-bottom:6px;">
    <div style="background:#fce4ec; padding:3px 8px; font-weight:bold; font-size:7pt;">TERAPÉUTICA EMPLEADA Y RESULTADOS</div>
    <div style="font-size:7.5pt; padding:4px 6px;">{{ $history->simple_therapeutics }}</div>
</div>
@endif

{{-- ── Pronóstico ── --}}
@if($history->simple_prognosis)
<div style="margin-bottom:6px; background:#f5f5f5; padding:5px 8px; font-size:7.5pt;">
    <strong>Pronóstico:</strong> {{ $history->simple_prognosis }}
</div>
@endif

{{-- ── Datos de elaboración ── --}}
@if($history->simple_elaboration_datetime)
<div style="font-size:7pt; margin-bottom:10px; color:#555;">
    <strong>Fecha de elaboración:</strong>
    {{ $history->simple_elaboration_datetime->format('d/m/Y H:i') }}
</div>
@endif

{{-- ── Firmas ── --}}
<table style="width:100%; margin-top:40px; font-size:8pt;">
    <tr>
        <td style="width:50%; text-align:center; padding:0 20px;">
            <div style="margin-bottom:30px;">&nbsp;</div>
            <div style="border-top:1px solid #333; padding-top:3px;">
                {!! nl2br(e($history->effectiveSignatureBlock())) !!}
            </div>
            <div style="font-size:7pt; color:#555;">Médico tratante</div>
        </td>
        @if($history->elaboratedBy)
        <td style="width:50%; text-align:center; padding:0 20px;">
            <div style="margin-bottom:30px;">&nbsp;</div>
            <div style="border-top:1px solid #333; padding-top:3px;">
                Dr(a). {{ $history->elaboratedBy->name }} {{ $history->elaboratedBy->last_name_one ?? '' }}
            </div>
            <div style="font-size:7pt; color:#555;">Elaboró</div>
        </td>
        @endif
    </tr>
</table>
@endsection
