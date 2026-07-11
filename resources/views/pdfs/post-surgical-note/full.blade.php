@extends('pdfs.layouts.base')

@section('document-title', '')
@section('document-subtitle', '')

@section('content')
<style>
    @page { margin: 90px 30px 50px 30px; }
    .doc-title { display:none; }
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
                NOTA POSTQUIRÚRGICA
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
            <strong>Médico:</strong> Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
        </td>
    </tr>
</table>

{{-- Datos de la cirugía --}}
<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;
              font-size:8.5pt; margin-bottom:8px;">
    <tr>
        <td style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Fecha de cirugía:</strong>
            {{ $note->surgery_date?->format('d/m/Y') ?? '—' }}
        </td>
        <td style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Hora:</strong>
            {{ $note->surgery_time ? \Illuminate\Support\Str::of($note->surgery_time)->substr(0, 5) : '—' }}
        </td>
        <td style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Tipo:</strong> {{ ucfirst($note->surgery_type ?? '—') }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Tiempo quirúrgico:</strong> {{ $note->surgical_time ?? '—' }}
        </td>
        <td style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Sangrado:</strong> {{ $note->bleeding ?? '—' }}
        </td>
        <td style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Isquemia:</strong> {{ $note->ischemia_time ?? 'No aplica' }}
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding:3px 6px; border:1px solid #ddd;">
            <strong>Recuento de textiles:</strong> {{ ucfirst($note->textile_count ?? '—') }}
            @if($note->textile_count_detail) — {{ $note->textile_count_detail }} @endif
        </td>
    </tr>
</table>

{{-- Diagnósticos --}}
@if(!empty(trim($note->preop_diagnosis ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong style="color:#E91E63;">Diagnóstico prequirúrgico:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->preop_diagnosis }}</span>
    </div>
@endif
@if(!empty(trim($note->postop_diagnosis ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong style="color:#E91E63;">Diagnóstico postquirúrgico:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->postop_diagnosis }}</span>
    </div>
@endif
@if(!empty(trim($note->planned_surgery ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong>Cirugía proyectada:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->planned_surgery }}</span>
    </div>
@endif
@if(!empty(trim($note->performed_surgery ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong>Cirugía realizada:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->performed_surgery }}</span>
    </div>
@endif
@if(!empty(trim($note->complications ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong>Complicaciones:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->complications }}</span>
    </div>
@endif
@if(!empty(trim($note->patient_status_at_exit ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong>Estado del paciente al salir de cirugía:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->patient_status_at_exit }}</span>
    </div>
@endif
@if(!empty(trim($note->prognosis ?? '')))
    <div style="margin-bottom:7px; font-size:9.5pt; line-height:1.4;">
        <strong>Pronóstico:</strong><br>
        <span style="white-space:pre-wrap;">{{ $note->prognosis }}</span>
    </div>
@endif

{{-- Equipo quirúrgico --}}
<div style="background-color:#f5f5f5; padding:6px 10px; margin:8px 0;
            font-size:9pt; border-left:3px solid #E91E63;">
    <strong>Equipo quirúrgico:</strong><br>
    Cirujano: {{ $note->surgeonName() }}<br>
    Ayudante/Instrumentista: {{ $note->assistantName() }}<br>
    Anestesiólogo: {{ $note->anesthesiologistName() }}
</div>

{{-- Técnica quirúrgica --}}
@if(!empty(trim($note->surgical_technique ?? '')))
    <div style="margin-bottom:8px; font-size:9.5pt; line-height:1.45;">
        <strong style="color:#E91E63;">Técnica quirúrgica:</strong><br>
        <div style="text-align:justify; font-size:9pt; white-space:pre-wrap;">{{ $note->surgical_technique }}</div>
    </div>
@endif

{{-- Firma --}}
<div style="margin-top:40px; text-align:center; font-size:9.5pt; line-height:1.6;">
    <div style="display:inline-block; border-top:1px solid #333; padding-top:4px; min-width:220px;">
        {!! nl2br(e($note->effectiveSignatureBlock())) !!}
    </div>
</div>

@endsection
