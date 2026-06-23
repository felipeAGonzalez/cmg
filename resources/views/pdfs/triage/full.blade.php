@extends('pdfs.layouts.base')

@section('document-title', '')

@section('content')
    <style>@page { margin: 100px 25px 55px 25px; }</style>

    {{-- Encabezado institucional rosa --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
        <tr>
            <td style="vertical-align:middle; text-align:center;">
                <div style="background-color:#E91E63; color:white;
                            padding:3px 8px; font-weight:bold;
                            font-size:9px; text-align:center;">
                    CLASIFICACI&Oacute;N DE PACIENTE PARA
                    ATENCI&Oacute;N EN EL SERVICIO DE URGENCIA
                </div>
                <div style="font-weight:bold; font-size:11px; text-align:center; margin-top:2px;">
                    HOJA DE TRIAGE
                </div>
            </td>
            <td style="width:80px; vertical-align:middle; padding-left:4px;">
                <div style="border:1px solid #E91E63; padding:2px 4px;
                            font-size:7px; text-align:center;">
                    <strong>FOLIO</strong><br>
                    <span style="font-size:9px;">{{ $triage->folio ?? '' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div style="text-align:right; font-size:6.5px; color:#555; margin-bottom:3px; line-height:1.2;">
        Privada Solar #3 Zona Centro Ac&aacute;mbaro, GTO. C.P. 38600 Tel. 01 (417) 172 04 30 y 172 81 30
    </div>

    {{-- Datos del paciente --}}
    <table style="width:100%; border:1px solid #333; border-collapse:collapse; margin-bottom:3px; font-size:7.5px;">
        <tr>
            <td style="padding:2px 4px; border:1px solid #333; width:50%; vertical-align:middle;">
                <strong>Fecha:</strong> {{ $triage->evaluation_started_at->format('d/m/Y') }}
            </td>
            <td style="padding:2px 4px; border:1px solid #333; width:50%; vertical-align:middle;">
                <strong>Hora inicio:</strong> {{ $triage->evaluation_started_at->format('H:i') }}
            </td>
        </tr>
        <tr>
            <td style="padding:2px 4px; border:1px solid #333; vertical-align:middle;">
                <strong>Nombre:</strong> {{ $patient->fullName() }}
            </td>
            <td style="padding:2px 4px; border:1px solid #333; vertical-align:middle;">
                <strong>Fecha nac.:</strong> {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '' }}
                @if($patient->birth_date) &mdash; {{ $patient->birth_date->age }} a&ntilde;os @endif
            </td>
        </tr>
    </table>

    {{-- Signos vitales --}}
    <div style="background-color:#FCE4EC; padding:2px 6px; font-weight:bold; text-align:center; font-size:8px; border:1px solid #333; border-bottom:none;">
        Signos Vitales
    </div>
    <table style="width:100%; border:1px solid #333; border-top:none; border-collapse:collapse; margin-bottom:3px; font-size:7.5px;">
        <tr>
            <td style="padding:2px 4px; border:1px solid #333; width:20%; vertical-align:middle;">
                <strong>F.C.:</strong> {{ $triage->heart_rate ?? '' }}
            </td>
            <td style="padding:2px 4px; border:1px solid #333; width:20%; vertical-align:middle;">
                <strong>T.A.:</strong> {{ $triage->blood_pressure_systolic ?? '' }}/{{ $triage->blood_pressure_diastolic ?? '' }}
            </td>
            <td style="padding:2px 4px; border:1px solid #333; width:20%; vertical-align:middle;">
                <strong>F.R.:</strong> {{ $triage->respiratory_rate ?? '' }}
            </td>
            <td style="padding:2px 4px; border:1px solid #333; width:20%; vertical-align:middle;">
                <strong>Temp.:</strong> {{ $triage->temperature ?? '' }}{{ $triage->temperature ? '°C' : '' }}
            </td>
            <td style="padding:2px 4px; border:1px solid #333; width:20%; vertical-align:middle;">
                <strong>SpO&sub2;:</strong> {{ $triage->oxygen_saturation ?? '' }}{{ $triage->oxygen_saturation ? '%' : '' }}
            </td>
        </tr>
    </table>

    {{-- Requiere atención inmediata --}}
    <div style="background-color:#B3D9FF; padding:2px 6px; font-weight:bold; text-align:center; font-size:8px; border:1px solid #333; border-bottom:none;">
        Requiere atenci&oacute;n inmediata
    </div>
    <table style="width:100%; border:1px solid #333; border-top:none; border-collapse:collapse; margin-bottom:3px; font-size:7px;">
        @php
            $immediates = [
                ['Pérdida súbita del estado de alerta', $triage->immediate_alert_loss],
                ['Apnea', $triage->immediate_apnea],
                ['Ausencia de pulso', $triage->immediate_no_pulse],
                ['Intubación de la vía aérea', $triage->immediate_intubation],
                ['Angor o equivalente anginoso', $triage->immediate_angina],
            ];
        @endphp
        @foreach($immediates as [$label, $value])
            <tr>
                <td style="padding:1px 4px; border:1px solid #333; vertical-align:middle;">
                    {{ $label }}
                </td>
                <td style="padding:1px 4px; border:1px solid #333; width:100px; text-align:center; vertical-align:middle;">
                    <strong>S&iacute;</strong>
                    <span style="border:1px solid #333; padding:0 2px; display:inline-block; width:10px; text-align:center; font-size:7px;">{!! $value ? 'X' : '&nbsp;' !!}</span>
                    &nbsp;
                    <strong>No</strong>
                    <span style="border:1px solid #333; padding:0 2px; display:inline-block; width:10px; text-align:center; font-size:7px;">{!! !$value ? 'X' : '&nbsp;' !!}</span>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- TABLA A --}}
    @php
        $tableA = [
            ['label' => 'Traumatismo', 'value' => $triage->trauma_score,
             'options' => [0 => 'Ausente', 5 => 'Menor', 10 => 'Moderado', 15 => 'Mayor']],
            ['label' => 'Herida', 'value' => $triage->wound_score,
             'options' => [0 => 'Ausente', 5 => 'Superficial', 10 => 'No penetrante', 15 => 'Ext./Prof.']],
            ['label' => 'Dificultad respiratoria', 'value' => $triage->respiratory_difficulty_score,
             'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
            ['label' => 'Cianosis', 'value' => $triage->cyanosis_score,
             'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
            ['label' => 'Palidez', 'value' => $triage->paleness_score,
             'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
            ['label' => 'Hemorragia', 'value' => $triage->hemorrhage_score,
             'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
            ['label' => 'Dolor (EVA)', 'value' => $triage->pain_score,
             'options' => [0 => '0', 5 => '1-4', 10 => '5-8', 15 => '9-10']],
            ['label' => 'Intoxicación / autodaño', 'value' => $triage->intoxication_score,
             'options' => [0 => 'Ausente', 10 => 'Dudosa', 15 => 'Evidente']],
            ['label' => 'Convulsiones', 'value' => $triage->seizures_score,
             'options' => [0 => 'Ausente', 10 => 'Postictal', 15 => 'Presentes']],
            ['label' => 'Glasgow', 'value' => $triage->glasgow_score,
             'options' => [0 => '15', 5 => '12-14', 10 => '8-11', 15 => '<8']],
            ['label' => 'Deshidratación', 'value' => $triage->dehydration_score,
             'options' => [0 => 'Ausente', 5 => 'Leve', 10 => 'Moderada', 15 => 'Severa']],
            ['label' => 'Psicosis / agitación', 'value' => $triage->psychosis_score,
             'options' => [0 => 'Ausente', 15 => 'Presente']],
        ];
    @endphp

    <table style="width:100%; border:1px solid #333; border-collapse:collapse; margin-bottom:3px; font-size:7px;">
        <thead>
            <tr style="background-color:#FCE4EC;">
                <th style="border:1px solid #333; padding:2px; width:24%; text-align:center; vertical-align:middle;">DATO</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; width:15%; vertical-align:middle;">0</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; width:15%; vertical-align:middle;">5</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; width:15%; vertical-align:middle;">10</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; width:15%; vertical-align:middle;">15</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; width:30px; vertical-align:middle;">PTS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableA as $row)
                <tr>
                    <td style="border:1px solid #333; padding:1px 3px; vertical-align:middle;">
                        <strong>{{ $row['label'] }}</strong>
                    </td>
                    @foreach([0, 5, 10, 15] as $points)
                        <td style="border:1px solid #333; padding:1px; text-align:center; vertical-align:middle;">
                            @if(array_key_exists($points, $row['options']))
                                <span style="border:1px solid #333; padding:0 2px; display:inline-block; width:10px; text-align:center;">{!! $row['value'] == $points ? 'X' : '&nbsp;' !!}</span>
                                <span style="font-size:5.5px;">{{ $row['options'][$points] }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td style="border:1px solid #333; padding:1px; text-align:center; vertical-align:middle; font-weight:bold;">
                        {{ $row['value'] }}
                    </td>
                </tr>
            @endforeach
            <tr style="background-color:#FCE4EC;">
                <td colspan="5" style="border:1px solid #333; padding:2px; text-align:right; vertical-align:middle;">
                    <strong>Suma parcial A</strong>
                </td>
                <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">
                    <strong>{{ $triage->sum_partial_a }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- TABLA B --}}
    @php
        $tableB = [
            ['label' => 'Tensión arterial', 'value' => $triage->bp_score,
             'ranges' => ['<70/50', '70-90/50-60', '91-120/61-80', '121-160/81-110', '>160/110']],
            ['label' => 'Frec. Cardíaca', 'value' => $triage->hr_score,
             'ranges' => ['<40', '41-60', '61-100', '101-140', '>140']],
            ['label' => 'Frec. respiratoria', 'value' => $triage->rr_score,
             'ranges' => ['<8', '9-12', '13-19', '20-25', '>25']],
            ['label' => 'Temperatura', 'value' => $triage->temp_score,
             'ranges' => ['<34.5', '34.5-35.9', '36-37.1', '37.1-39', '>39']],
            ['label' => 'Glucemia capilar', 'value' => $triage->glucose_score,
             'ranges' => ['<40', '40-60', '61-130', '131-400', '>400']],
        ];
        $bPointsCols = [10, 5, 0, 5, 10];
    @endphp

    <table style="width:100%; border:1px solid #333; border-collapse:collapse; margin-bottom:3px; font-size:7px;">
        <thead>
            <tr style="background-color:#FCE4EC;">
                <th style="border:1px solid #333; padding:2px; width:18%; text-align:center; vertical-align:middle;">PAR&Aacute;METRO</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">10</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">5</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">0</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">5</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">10</th>
                <th style="border:1px solid #333; padding:2px; text-align:center; width:30px; vertical-align:middle;">PTS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableB as $row)
                <tr>
                    <td style="border:1px solid #333; padding:1px 3px; vertical-align:middle;">
                        <strong>{{ $row['label'] }}</strong>
                    </td>
                    @foreach($row['ranges'] as $i => $range)
                        @php $colPts = $bPointsCols[$i]; @endphp
                        <td style="border:1px solid #333; padding:1px; text-align:center; vertical-align:middle;">
                            <span style="border:1px solid #333; padding:0 2px; display:inline-block; width:10px; text-align:center;">{!! ($row['value'] == $colPts && ($colPts == 0 || ($colPts > 0))) ? 'X' : '&nbsp;' !!}</span>
                            <span style="font-size:5.5px;">{{ $range }}</span>
                        </td>
                    @endforeach
                    <td style="border:1px solid #333; padding:1px; text-align:center; vertical-align:middle; font-weight:bold;">
                        {{ $row['value'] }}
                    </td>
                </tr>
            @endforeach
            <tr style="background-color:#FCE4EC;">
                <td colspan="6" style="border:1px solid #333; padding:2px; text-align:right; vertical-align:middle;">
                    <strong>Suma parcial B</strong>
                </td>
                <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">
                    <strong>{{ $triage->sum_partial_b }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Clasificación para toma de decisión --}}
    <div style="background-color:#FCE4EC; padding:2px 6px; font-weight:bold; text-align:center; font-size:8px; border:1px solid #333; border-bottom:none;">
        CLASIFICACI&Oacute;N PARA TOMA DE DECISI&Oacute;N
    </div>

    @php
        $classifications = [
            ['color' => 'blue', 'label' => 'Azul', 'bg' => '#2196F3', 'fg' => '#fff',
             'score' => '0-10', 'decision' => 'Sin urgencia', 'time' => '121-240', 'site' => 'Consultorio'],
            ['color' => 'green', 'label' => 'Verde', 'bg' => '#4CAF50', 'fg' => '#fff',
             'score' => '11-20', 'decision' => 'Urg. menor', 'time' => '61-120', 'site' => 'Primer contacto'],
            ['color' => 'yellow', 'label' => 'Amarillo', 'bg' => '#FFEB3B', 'fg' => '#000',
             'score' => '21-30', 'decision' => 'Urgencia', 'time' => '30-60', 'site' => 'Observación'],
            ['color' => 'orange', 'label' => 'Naranja', 'bg' => '#FF9800', 'fg' => '#fff',
             'score' => '31-40', 'decision' => 'Emergencia', 'time' => '10', 'site' => 'Estabilización'],
            ['color' => 'red', 'label' => 'Rojo', 'bg' => '#F44336', 'fg' => '#fff',
             'score' => '>40', 'decision' => 'Reanimación', 'time' => 'Inmed.', 'site' => 'Choque'],
        ];
    @endphp

    <table style="width:100%; border:1px solid #333; border-top:none; border-collapse:collapse; margin-bottom:4px; font-size:6.5px;">
        <tr>
            <td style="border:1px solid #333; padding:2px; width:14%; text-align:center; vertical-align:middle;"><strong>Color</strong></td>
            @foreach($classifications as $cls)
                <td style="border:1px solid #333; padding:2px; background-color:{{ $cls['bg'] }}; color:{{ $cls['fg'] }}; text-align:center; vertical-align:middle; {{ $triage->color === $cls['color'] ? 'border:3px solid #000;' : '' }}">
                    <strong>{{ $cls['label'] }}</strong>
                </td>
            @endforeach
        </tr>
        <tr>
            <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;"><strong>Pts A+B</strong></td>
            @foreach($classifications as $cls)
                <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">{{ $cls['score'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;"><strong>Decisi&oacute;n</strong></td>
            @foreach($classifications as $cls)
                <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">{{ $cls['decision'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;"><strong>Tiempo (min)</strong></td>
            @foreach($classifications as $cls)
                <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">{{ $cls['time'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;"><strong>Sitio</strong></td>
            @foreach($classifications as $cls)
                <td style="border:1px solid #333; padding:2px; text-align:center; vertical-align:middle;">{{ $cls['site'] }}</td>
            @endforeach
        </tr>
    </table>

    {{-- Total y resultado --}}
    <table style="width:100%; margin-bottom:4px; font-size:8px;">
        <tr>
            <td style="padding:2px 4px; vertical-align:middle; width:50%;">
                <strong>Total A+B:</strong> {{ $triage->total_score }}
            </td>
            <td style="padding:2px 4px; text-align:right; vertical-align:middle; width:50%;">
                <strong>Clasificaci&oacute;n:</strong>
                @php
                    $bgColor = match($triage->color) {
                        'red' => '#F44336', 'orange' => '#FF9800', 'yellow' => '#FFEB3B',
                        'green' => '#4CAF50', 'blue' => '#2196F3', default => '#999',
                    };
                    $fgColor = $triage->color === 'yellow' ? '#000' : '#fff';
                @endphp
                <span style="background-color:{{ $bgColor }}; color:{{ $fgColor }}; padding:2px 8px; font-weight:bold;">
                    {{ $triage->colorLabel() }} - {{ $triage->decisionLabel() }}
                </span>
            </td>
        </tr>
    </table>

    @if($triage->hasImmediateAlert())
        <div style="background-color:#F44336; color:white; padding:3px 8px; text-align:center; font-weight:bold; font-size:7.5px; margin-bottom:4px;">
            ATENCI&Oacute;N INMEDIATA REQUERIDA &mdash; Clasificaci&oacute;n forzada a Rojo
        </div>
    @endif

    {{-- Hora de término + firmas --}}
    <table style="width:100%; font-size:7.5px; margin-top:6px;">
        <tr>
            <td style="padding:2px 4px; text-align:center;" colspan="2">
                <strong>Hora de t&eacute;rmino de evaluaci&oacute;n:</strong>
                {{ $triage->evaluation_ended_at?->format('d/m/Y H:i') ?? '____________' }}
            </td>
        </tr>
    </table>

    <table style="width:100%; font-size:7.5px; margin-top:6px;">
        <tr>
            <td style="width:50%; text-align:center; vertical-align:top; padding:0 10px;">
                _________________________________<br>
                <strong>NOMBRE DEL QUE REALIZ&Oacute;</strong>
                <div style="font-size:7px; color:#555; margin-top:1px;">
                    {{ $triage->performedBy->fullName() ?? '' }}
                    @if($triage->performedBy?->professional_license)
                        <br>Céd. {{ $triage->performedBy->professional_license }}
                    @endif
                </div>
            </td>
            <td style="width:50%; text-align:center; vertical-align:top; padding:0 10px;">
                _________________________________<br>
                <strong>FIRMA</strong>
            </td>
        </tr>
    </table>

    <div style="margin-top:4px; font-size:6.5px; color:#999; text-align:center;">
        Documento generado el {{ $generatedAt->format('d/m/Y H:i') }}.
    </div>
@endsection
