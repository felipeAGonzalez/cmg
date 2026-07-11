@extends('pdfs.layouts.base')

@section('document-title', '')
@section('document-subtitle', '')

@section('content')
<style>
    @page { margin: 90px 30px 50px 30px; }
    .doc-title { display:none; }
    .vitals-box { background:#f9f0f4; border:1px solid #f0b8cc; padding:6px 10px; font-size:8.5pt; }
    .vitals-cell { display:inline-block; margin-right:14px; }
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
                NOTA TRANSFUSIONAL
            </div>
            <div style="text-align:right; font-size:7pt; color:#666; margin-top:3px;">
                Privada Solar #3 Zona Centro Acámbaro, GTO. | C.P. 38600 | Tel. 01 (417) 172 04 30
            </div>
        </td>
    </tr>
</table>

{{-- Datos del paciente --}}
<table style="width:100%; border:1px solid #333; border-collapse:collapse;
              margin-bottom:10px; font-size:9pt;">
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
        <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
            <strong>Nº Expediente:</strong> {{ $patient->id }}
        </td>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; border-left:1px solid #ddd;">
            <strong>Médico:</strong> Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px;">
            <strong>Inicio de transfusión:</strong>
            {{ $note->start_datetime ? $note->start_datetime->format('d/m/Y H:i') : '—' }}
        </td>
        <td style="padding:3px 6px; border-left:1px solid #ddd;">
            <strong>Término:</strong>
            {{ $note->end_datetime ? $note->end_datetime->format('H:i') : '—' }}
            @if($note->transfusionChecklist)
                &nbsp;&nbsp;<strong>Folio LV:</strong> {{ $note->transfusionChecklist->folio ?? $note->transfusion_checklist_id }}
            @endif
        </td>
    </tr>
</table>

{{-- Signos vitales PREVIOS --}}
@php
    $hasPreVitals = $note->pre_ta || $note->pre_fc || $note->pre_fr || $note->pre_temp || $note->pre_spo2;
@endphp
@if($hasPreVitals)
    <div class="vitals-box" style="margin-bottom:8px; border-left:3px solid #E91E63;">
        <strong>Signos vitales PREVIOS:</strong>&nbsp;
        @if($note->pre_ta)<span class="vitals-cell">TA: {{ $note->pre_ta }} mmHg</span>@endif
        @if($note->pre_fc)<span class="vitals-cell">FC: {{ $note->pre_fc }} lpm</span>@endif
        @if($note->pre_fr)<span class="vitals-cell">FR: {{ $note->pre_fr }} rpm</span>@endif
        @if($note->pre_temp)<span class="vitals-cell">Temp: {{ $note->pre_temp }} °C</span>@endif
        @if($note->pre_spo2)<span class="vitals-cell">SpO₂: {{ $note->pre_spo2 }}%</span>@endif
    </div>
@endif

{{-- Secciones narrativas (diagnoses_and_indication, compatibility_verification) --}}
@foreach(['diagnoses_and_indication', 'compatibility_verification'] as $key)
    @php $content = $note->{$key}; @endphp
    @if(!empty(trim($content ?? '')))
        <div style="margin-bottom:8px; font-size:9.5pt; line-height:1.45;">
            <strong style="color:#E91E63;">{{ $sections[$key]['label'] }}:</strong><br>
            <span style="white-space:pre-wrap;">{{ $content }}</span>
        </div>
    @endif
@endforeach

{{-- Sección: Evolución --}}
@php $content = $note->evolution_narrative; @endphp
@if(!empty(trim($content ?? '')))
    <div style="margin-bottom:8px; font-size:9.5pt; line-height:1.45;">
        <strong style="color:#E91E63;">{{ $sections['evolution_narrative']['label'] }}:</strong><br>
        <span style="white-space:pre-wrap;">{{ $content }}</span>
    </div>
@endif

{{-- Signos vitales POSTERIORES --}}
@php
    $hasPostVitals = $note->post_ta || $note->post_fc || $note->post_fr || $note->post_temp || $note->post_spo2;
@endphp
@if($hasPostVitals)
    <div class="vitals-box" style="margin:8px 0; border-left:3px solid #E91E63;">
        <strong>Signos vitales POSTERIORES:</strong>&nbsp;
        @if($note->post_ta)<span class="vitals-cell">TA: {{ $note->post_ta }} mmHg</span>@endif
        @if($note->post_fc)<span class="vitals-cell">FC: {{ $note->post_fc }} lpm</span>@endif
        @if($note->post_fr)<span class="vitals-cell">FR: {{ $note->post_fr }} rpm</span>@endif
        @if($note->post_temp)<span class="vitals-cell">Temp: {{ $note->post_temp }} °C</span>@endif
        @if($note->post_spo2)<span class="vitals-cell">SpO₂: {{ $note->post_spo2 }}%</span>@endif
    </div>
@endif

{{-- Conclusión --}}
@php $content = $note->conclusion; @endphp
@if(!empty(trim($content ?? '')))
    <div style="margin-bottom:8px; font-size:9.5pt; line-height:1.45;">
        <strong style="color:#E91E63;">{{ $sections['conclusion']['label'] }}:</strong><br>
        <span style="white-space:pre-wrap;">{{ $content }}</span>
    </div>
@endif

{{-- Firma del médico --}}
<div style="margin-top:40px; text-align:center; font-size:9.5pt; line-height:1.6;">
    <div style="display:inline-block; border-top:1px solid #333; padding-top:4px; min-width:220px;">
        {!! nl2br(e($note->effectiveSignatureBlock())) !!}
    </div>
</div>

@endsection
