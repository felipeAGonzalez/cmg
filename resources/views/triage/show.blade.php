@extends('layouts.app')

@section('content')
<div class="container py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-clipboard-pulse"></i> Hoja de Triage</h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $triage->patient->fullName() }}</strong>
                @if($triage->patient->birth_date)
                    &middot; {{ $triage->patient->birth_date->age }} a&ntilde;os
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('triage.pdf', $triage) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Ver PDF
            </a>
            <a href="{{ route('triage.edit', $triage) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil"></i> Editar
            </a>
        </div>
    </div>

    {{-- Clasificación final --}}
    @php
        $borderClass = match($triage->color) {
            'red' => 'border-danger',
            'orange' => 'border-warning',
            'yellow' => 'border-warning',
            'green' => 'border-success',
            'blue' => 'border-primary',
            default => '',
        };
    @endphp
    <div class="card mb-4 border-3 {{ $borderClass }}">
        <div class="card-body text-center">
            <span class="badge {{ $triage->colorBadgeClass() }} fs-2 mb-2">
                {{ $triage->colorLabel() }}
            </span>
            <h4 class="mb-1">{{ $triage->decisionLabel() }}</h4>
            <p class="text-muted mb-0">
                Atenci&oacute;n: {{ $triage->attentionTimeLabel() }} &middot;
                Sitio: {{ $triage->siteLabel() }} &middot;
                Puntaje total: {{ $triage->total_score }}
                (A: {{ $triage->sum_partial_a }} + B: {{ $triage->sum_partial_b }})
            </p>
            @if($triage->hasImmediateAlert())
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Clasificaci&oacute;n forzada a Rojo por criterio de atenci&oacute;n inmediata.
                </div>
            @endif
            <div class="mt-3 small text-muted">
                Sugerencia autom&aacute;tica: <strong>{{ $triage->suggestedDestinationLabel() }}</strong>
            </div>
            <div class="mt-2">
                <span class="badge {{ $triage->dispositionBadgeClass() }}">
                    Disposici&oacute;n: {{ $triage->dispositionLabel() }}
                </span>
                @if($triage->disposition_at)
                    <span class="small text-muted ms-2">
                        {{ $triage->disposition_at->format('d/m/Y H:i') }}
                        @if($triage->dispositionBy)
                            por {{ $triage->dispositionBy->fullName() }}
                        @endif
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Datos administrativos --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Datos administrativos</h6></div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Folio:</dt>
                <dd class="col-sm-9">{{ $triage->folio ?? '—' }}</dd>

                <dt class="col-sm-3">Inicio evaluaci&oacute;n:</dt>
                <dd class="col-sm-9">{{ $triage->evaluation_started_at->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">T&eacute;rmino evaluaci&oacute;n:</dt>
                <dd class="col-sm-9">{{ $triage->evaluation_ended_at?->format('d/m/Y H:i') ?? '—' }}</dd>

                <dt class="col-sm-3">Realiz&oacute;:</dt>
                <dd class="col-sm-9">{{ $triage->performedBy->fullName() }}</dd>
            </dl>
        </div>
    </div>

    {{-- Signos vitales --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Signos vitales</h6></div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">F.C.:</dt>
                <dd class="col-sm-3">{{ $triage->heart_rate ? $triage->heart_rate . ' lpm' : '—' }}</dd>
                <dt class="col-sm-3">T.A.:</dt>
                <dd class="col-sm-3">
                    {{ $triage->blood_pressure_systolic ?? '—' }}/{{ $triage->blood_pressure_diastolic ?? '—' }} mmHg
                </dd>

                <dt class="col-sm-3">F.R.:</dt>
                <dd class="col-sm-3">{{ $triage->respiratory_rate ? $triage->respiratory_rate . ' rpm' : '—' }}</dd>
                <dt class="col-sm-3">Temperatura:</dt>
                <dd class="col-sm-3">{{ $triage->temperature ? $triage->temperature . ' °C' : '—' }}</dd>

                <dt class="col-sm-3">Sat. O&sub2;:</dt>
                <dd class="col-sm-3">{{ $triage->oxygen_saturation ? $triage->oxygen_saturation . '%' : '—' }}</dd>
                <dt class="col-sm-3">Glucemia:</dt>
                <dd class="col-sm-3">{{ $triage->glucose_mg_dl ? $triage->glucose_mg_dl . ' mg/dL' : '—' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Criterios de atención inmediata --}}
    @if($triage->hasImmediateAlert())
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">Criterios de atenci&oacute;n inmediata presentes</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    @if($triage->immediate_alert_loss)<li>P&eacute;rdida s&uacute;bita del estado de alerta</li>@endif
                    @if($triage->immediate_apnea)<li>Apnea</li>@endif
                    @if($triage->immediate_no_pulse)<li>Ausencia de pulso</li>@endif
                    @if($triage->immediate_intubation)<li>Intubaci&oacute;n de la v&iacute;a a&eacute;rea</li>@endif
                    @if($triage->immediate_angina)<li>Angor o equivalente anginoso</li>@endif
                </ul>
            </div>
        </div>
    @endif

    {{-- Detalle Tabla A --}}
    @php
        $detailA = [
            'trauma_score' => 'Traumatismo',
            'wound_score' => 'Herida',
            'respiratory_difficulty_score' => 'Dificultad respiratoria',
            'cyanosis_score' => 'Cianosis',
            'paleness_score' => 'Palidez',
            'hemorrhage_score' => 'Hemorragia',
            'pain_score' => 'Dolor',
            'intoxication_score' => 'Intoxicación o autodaño',
            'seizures_score' => 'Convulsiones',
            'glasgow_score' => 'Glasgow',
            'dehydration_score' => 'Deshidratación',
            'psychosis_score' => 'Psicosis/agitación',
        ];
    @endphp
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Calificaci&oacute;n A &mdash; Datos cl&iacute;nicos</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Dato</th>
                        <th class="text-center" style="width:100px;">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailA as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-center">
                                <span class="badge {{ $triage->$field > 0 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                    {{ $triage->$field }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td class="text-end"><strong>Suma parcial A:</strong></td>
                        <td class="text-center"><span class="badge bg-primary">{{ $triage->sum_partial_a }}</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Detalle Tabla B --}}
    @php
        $detailB = [
            'bp_score' => 'Tensión arterial',
            'hr_score' => 'Frecuencia cardíaca',
            'rr_score' => 'Frecuencia respiratoria',
            'temp_score' => 'Temperatura',
            'glucose_score' => 'Glucemia capilar',
        ];
    @endphp
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Calificaci&oacute;n B &mdash; Par&aacute;metros fisiol&oacute;gicos</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Par&aacute;metro</th>
                        <th class="text-center" style="width:100px;">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailB as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-center">
                                <span class="badge {{ $triage->$field > 0 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                    {{ $triage->$field }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td class="text-end"><strong>Suma parcial B:</strong></td>
                        <td class="text-center"><span class="badge bg-primary">{{ $triage->sum_partial_b }}</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4 mb-5">
        <a href="{{ route('waitingRoom.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Sala de Espera
        </a>
    </div>
</div>

<style>
    .bg-orange { background-color: #fd7e14 !important; color: #000 !important; }
</style>
@endsection
