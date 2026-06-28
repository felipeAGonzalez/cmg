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

    $gender = strtoupper($patient->gender ?? '');
    $genderLabel = ($gender === 'M' || $gender === 'MASCULINO') ? 'Masculino' : 'Femenino';
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
                NOTA DE EVOLUCIÓN
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
        <td style="padding:3px 6px;">
            <strong>Nº Expediente:</strong> {{ $patient->id }}
        </td>
        <td style="padding:3px 6px; border-left:1px solid #ddd;">
            <strong>Fecha de nota:</strong> {{ $note->note_datetime->format('d/m/Y H:i') }}
        </td>
    </tr>
</table>

{{-- Secciones SOAP --}}
@foreach($sections as $key => $config)
    @php $content = $note->{$key}; @endphp
    @if(!empty(trim($content ?? '')))
        <div style="margin-bottom:8px; font-size:9.5pt; line-height:1.45;">
            <strong style="color:#E91E63;">{{ $config['label'] }}:</strong>
            <span style="white-space:pre-wrap;">{{ $content }}</span>
        </div>
    @endif
@endforeach

{{-- Signos vitales --}}
@if($latestVitals)
    <div style="background-color:#f9f0f4; padding:5px 10px; margin:10px 0;
                font-size:8.5pt; border-left:3px solid #E91E63;">
        <strong>Signos vitales</strong>
        <span style="font-size:7.5pt; color:#666;">
            (último registro: {{ $latestVitals->recorded_at->format('d/m/Y H:i') }})
        </span><br>
        FC: {{ $latestVitals->heart_rate ?? '—' }} lpm
        &nbsp;&nbsp;TA: {{ $latestVitals->blood_pressure_systolic ?? '—' }}/{{ $latestVitals->blood_pressure_diastolic ?? '—' }} mmHg
        &nbsp;&nbsp;Temp: {{ $latestVitals->temperature ?? '—' }} °C
        &nbsp;&nbsp;FR: {{ $latestVitals->respiratory_rate ?? '—' }} rpm
    </div>
@endif

{{-- Medicamentos administrados --}}
@if($medications->isNotEmpty())
    <div style="margin:10px 0;">
        <strong style="font-size:9.5pt;">Medicamentos administrados</strong>
        <span style="font-size:8pt; color:#666;">
            ({{ $note->medications_from->format('d/m/Y H:i') }}
            a {{ $note->medications_to->format('d/m/Y H:i') }})
        </span>
        <table style="width:100%; border:1px solid #333; border-collapse:collapse;
                      margin-top:4px; font-size:8pt;">
            <thead>
                <tr style="background-color:#f5f5f5;">
                    <th style="padding:3px 6px; border:1px solid #ccc; text-align:left;">Medicamento</th>
                    <th style="padding:3px 6px; border:1px solid #ccc; text-align:left;">Dosis</th>
                    <th style="padding:3px 6px; border:1px solid #ccc; text-align:left;">Vía</th>
                    <th style="padding:3px 6px; border:1px solid #ccc; text-align:left;">Hora</th>
                    <th style="padding:3px 6px; border:1px solid #ccc; text-align:left;">Enfermera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medications as $adm)
                    <tr>
                        <td style="padding:3px 6px; border:1px solid #ccc;">{{ $adm->medicationOrder->medication_name ?? '—' }}</td>
                        <td style="padding:3px 6px; border:1px solid #ccc;">{{ $adm->medicationOrder->dose ?? '—' }}</td>
                        <td style="padding:3px 6px; border:1px solid #ccc;">{{ $adm->medicationOrder->routeLabel() ?? '—' }}</td>
                        <td style="padding:3px 6px; border:1px solid #ccc;">{{ $adm->administered_at?->format('d/m H:i') ?? '—' }}</td>
                        <td style="padding:3px 6px; border:1px solid #ccc;">{{ $adm->recordedBy?->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Firma del médico --}}
<div style="margin-top:40px; text-align:center; font-size:9.5pt; line-height:1.6;">
    <div style="display:inline-block; border-top:1px solid #333; padding-top:4px; min-width:220px;">
        {!! nl2br(e($note->effectiveSignatureBlock())) !!}
    </div>
</div>

@endsection
