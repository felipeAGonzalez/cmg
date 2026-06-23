@extends('pdfs.layouts.base')

@section('document-title', 'HISTORIA CLÍNICA')

@section('content')
    @php
        $g = strtoupper($patient->gender ?? '');
        $isMale = $g === 'M' || $g === 'MASCULINO';
        $isFemale = $g === 'F' || $g === 'FEMENINO';
        $patientAge = $patient->birth_date
            ? $patient->birth_date->age
            : '';
    @endphp

    <table style="width:100%; border:1px solid #333; border-collapse:collapse;
                  margin-bottom:10px; font-size:9px;">
        <tr>
            <td style="padding:3px 6px; border:1px solid #ddd; width:60%;">
                <strong>NOMBRE:</strong> {{ $patient->fullName() }}
            </td>
            <td style="padding:3px 6px; border:1px solid #ddd;">
                <strong>EDAD:</strong> {{ $patientAge ? $patientAge . ' años' : '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px; border:1px solid #ddd;">
                <strong>FECHA DE NACIMIENTO:</strong>
                {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '—' }}
            </td>
            <td style="padding:3px 6px; border:1px solid #ddd;">
                <strong>SEXO:</strong>
                @if($isMale) Masculino @elseif($isFemale) Femenino @else — @endif
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px; border:1px solid #ddd;">
                <strong>CAMA:</strong> {{ $stay->room->number ?? '—' }}
            </td>
            <td style="padding:3px 6px; border:1px solid #ddd;">
                <strong>FECHA DE INGRESO:</strong>
                {{ $stay->admission_date ? $stay->admission_date->format('d/m/Y H:i') : '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px; border:1px solid #ddd;" colspan="2">
                <strong>DIAGNÓSTICO:</strong> {{ $stay->diagnosis ?? '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px;" colspan="2">
                <strong>MÉDICO TRATANTE:</strong>
                Dr(a). {{ $history->attendingDoctor->name ?? '' }}
                {{ $history->attendingDoctor->last_name_one ?? '' }}
                @if($history->attendingDoctor?->specialtiesLabel())
                    — {{ $history->attendingDoctor->specialtiesLabel() }}
                @endif
                @if($history->attendingDoctor?->professional_license)
                    (Céd. {{ $history->attendingDoctor->professional_license }})
                @endif
            </td>
        </tr>
    </table>

    @foreach($sections as $key => $section)
        @php $content = $history->{$key} ?? ''; @endphp
        @if(!empty(trim($content)) && $key !== 'signature_block')
            <div style="margin-bottom:10px;">
                <div style="font-size:10px; font-weight:bold;
                            background-color:#FCE4EC; padding:3px 6px;
                            margin-bottom:4px;">
                    {{ strtoupper($section['label']) }}
                </div>
                <div style="font-size:9px; line-height:1.5;
                            text-align:justify; white-space:pre-wrap;
                            padding:0 6px;">{{ $content }}</div>
            </div>
        @endif
    @endforeach

    @if($instructions->isNotEmpty())
        <div style="page-break-before:always;">
            <div style="background-color:#E91E63; color:white;
                        padding:5px 10px; margin-bottom:10px;
                        font-weight:bold; text-align:center; font-size:11px;">
                ANEXO: INDICACIONES MÉDICAS
            </div>

            <table style="width:100%; border:1px solid #333; border-collapse:collapse;
                          font-size:9px;">
                <thead>
                    <tr style="background-color:#E8E8F0;">
                        <th style="border:1px solid #333; padding:4px; width:18%;
                                   text-align:center;">
                            FECHA Y HORA
                        </th>
                        <th style="border:1px solid #333; padding:4px; text-align:center;">
                            INDICACIÓN MÉDICA
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instructions as $instruction)
                        <tr>
                            <td style="border:1px solid #333; padding:4px 6px;
                                       vertical-align:top; white-space:nowrap;">
                                {{ $instruction->created_at->format('d/m/Y') }}<br>
                                <span style="font-size:8px; color:#555;">
                                    {{ $instruction->created_at->format('H:i') }}
                                </span>
                            </td>
                            <td style="border:1px solid #333; padding:4px 6px;
                                       vertical-align:top; white-space:pre-wrap;">{{ $instruction->body }}
                                @if($instruction->doctor)
                                    <div style="font-size:8px; color:#666;
                                                margin-top:2px; text-align:right;">
                                        — Dr(a). {{ $instruction->doctor->name }}
                                        {{ $instruction->doctor->last_name_one ?? '' }}
                                        @if($instruction->doctor->professional_license)
                                            (Céd. {{ $instruction->doctor->professional_license }})
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div style="margin-top:30px; font-size:9px;">
        <div style="text-align:center; border-top:1px solid #333;
                    padding-top:5px; width:60%; margin:0 auto;
                    white-space:pre-wrap;">
            <strong>{{ $history->effectiveSignatureBlock() }}</strong>
        </div>
    </div>
@endsection
