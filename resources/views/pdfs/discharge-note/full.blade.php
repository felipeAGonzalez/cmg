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
                        font-weight:bold; font-size:12pt; text-align:center; letter-spacing:.4px;">
                NOTA DE ALTA
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
            {{ \Carbon\Carbon::parse($stay->admission_date)->format('d/m/Y H:i') }}
        </td>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; border-left:1px solid #ddd;">
            <strong>F. Egreso:</strong>
            {{ $stay->discharge_date ? $stay->discharge_date->format('d/m/Y H:i') : '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
            <strong>Nº Expediente:</strong> {{ $patient->id }}
        </td>
        <td style="padding:3px 6px; border-bottom:1px solid #ddd; border-left:1px solid #ddd;">
            <strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 6px;" colspan="2">
            <strong>Médico tratante:</strong>
            Dr(a). {{ $note->attendingDoctor?->name ?? '' }}
            {{ $note->attendingDoctor?->last_name_one ?? '' }}
            @if($note->attendingDoctor?->specialtiesLabel())
                — {{ $note->attendingDoctor->specialtiesLabel() }}
            @endif
            @if($note->attendingDoctor?->professional_license)
                (Céd. {{ $note->attendingDoctor->professional_license }})
            @endif
        </td>
    </tr>
</table>

{{-- 6 secciones narrativas (solo las que tienen contenido) --}}
@foreach($sections as $key => $config)
    @if(!empty(trim($note->{$key} ?? '')))
        <div style="margin-bottom:10px;">
            <div style="background-color:#fce4ec; padding:3px 8px;
                        font-weight:bold; color:#c2185b; font-size:10pt;
                        border-left:3px solid #E91E63;">
                {{ $config['label'] }}
            </div>
            <div style="padding:6px 10px; font-size:9.5pt; text-align:justify;
                        border:1px solid #ddd; border-top:none; line-height:1.5;
                        white-space:pre-wrap;">{{ $note->{$key} }}</div>
        </div>
    @endif
@endforeach

{{-- Firma del médico (a mano sobre PDF impreso) --}}
<div style="margin-top:50px; text-align:center; font-size:9.5pt; line-height:1.6;">
    <div style="display:inline-block; border-top:1px solid #333; padding-top:4px; min-width:260px;">
        {!! nl2br(e($note->effectiveSignatureBlock())) !!}
    </div>
</div>

@endsection
