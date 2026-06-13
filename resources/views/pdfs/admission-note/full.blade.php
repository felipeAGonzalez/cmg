@extends('pdfs.layouts.base')

@section('document-title', 'Nota de Ingreso')

@section('content')
    @php
        use Carbon\Carbon;

        $admission = $stay->admission_date ? Carbon::parse($stay->admission_date) : null;
        $discharge = $stay->discharge_date ? Carbon::parse($stay->discharge_date) : null;
        $genderLabel = match (strtoupper($patient->gender ?? '')) {
            'M', 'MASCULINO' => 'Masculino',
            'F', 'FEMENINO'  => 'Femenino',
            default          => '—',
        };
    @endphp

    {{-- Encabezado compacto con datos del paciente en 2 columnas --}}
    <table style="width:100%; border:1px solid #333; border-collapse:collapse; margin-bottom:10px; font-size:9px;">
        <tr>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd; width:50%;">
                <strong>NOMBRE:</strong> {{ $patient->fullName() }}
            </td>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd; width:50%;">
                <strong>EDAD:</strong> {{ $patient->birth_date ? $patient->age() . ' años' : '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
                <strong>FECHA DE NACIMIENTO:</strong> {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '—' }}
            </td>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
                <strong>SEXO:</strong> {{ $genderLabel }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
                <strong>NO. DE CAMA:</strong> {{ $stay->room->number ?? '—' }}
            </td>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
                <strong>FECHA DE INGRESO:</strong> {{ $admission ? $admission->format('d/m/Y H:i') : '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
                <strong>FECHA DE EGRESO:</strong> {{ $discharge ? $discharge->format('d/m/Y H:i') : '—' }}
            </td>
            <td style="padding:3px 6px; border-bottom:1px solid #ddd;">
                <strong>DIAGNÓSTICO:</strong> {{ $stay->diagnosis ?: '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding:3px 6px;" colspan="2">
                <strong>MÉDICO(S) TRATANTE(S):</strong>
                @forelse($stay->currentDoctors as $sd)
                    Dr(a). {{ $sd->doctor?->fullName() ?? '—' }}@if($sd->doctor?->specialtiesLabel()) — {{ $sd->doctor->specialtiesLabel() }}@endif{{ ! $loop->last ? ' · ' : '' }}
                @empty
                    —
                @endforelse
            </td>
        </tr>
    </table>

    {{-- Tabla cronológica de notas de indicaciones médicas --}}
    <table style="width:100%; border:1px solid #333; border-collapse:collapse; font-size:9px;">
        <thead>
            <tr style="background-color:#E8E8F0;">
                <th style="border:1px solid #333; padding:4px; width:18%; text-align:center;">FECHA Y HORA</th>
                <th style="border:1px solid #333; padding:4px; text-align:center;">NOTAS DE INDICACIONES MÉDICAS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($instructions as $instruction)
                <tr>
                    <td style="border:1px solid #333; padding:4px 6px; vertical-align:top; white-space:nowrap;">
                        {{ $instruction->created_at->format('d/m/Y') }} <span style="font-size:8px; color:#555;">{{ $instruction->created_at->format('H:i') }}</span>
                    </td>
                    <td style="border:1px solid #333; padding:4px 6px; vertical-align:top; white-space:pre-wrap;">{{ $instruction->body }}@if($instruction->doctor)<div style="font-size:8px; color:#666; margin-top:2px; text-align:right;">— Dr(a). {{ $instruction->doctor->fullName() }}</div>@endif</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="border:1px solid #333; padding:12px; text-align:center; color:#777; font-style:italic;">
                        Sin indicaciones médicas registradas durante la estancia.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:8px; font-size:8px; color:#777;">
        Documento generado el {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy->fullName() }}.
    </div>
@endsection
