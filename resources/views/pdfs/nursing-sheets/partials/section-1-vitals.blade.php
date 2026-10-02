@php
    use App\Support\Shift;
@endphp

<div class="chapter-title">Hoja 1 — Registros clínicos y signos vitales</div>

{{-- La gráfica de signos vitales vive ahora al pie del cover (página 1). --}}

{{-- Tabla cronológica única de tomas de signos vitales --}}
<div style="margin-bottom:16px;">
    <div class="subsection-title">Tomas de signos vitales ({{ $vitalSignReadings->count() }})</div>
    @if($vitalSignReadings->isEmpty())
        <p class="empty-note">Sin tomas de signos vitales registradas durante la estancia.</p>
    @else
        <table class="grid">
            <thead>
                <tr>
                    <th class="center" style="width:62px;">Fecha</th>
                    <th class="center" style="width:42px;">Hora</th>
                    <th class="center" style="width:54px;">Turno</th>
                    <th class="center" style="width:42px;">F.C.</th>
                    <th class="center" style="width:58px;">T.A.</th>
                    <th class="center" style="width:42px;">F.R.</th>
                    <th class="center" style="width:48px;">Temp.</th>
                    <th>Notas</th>
                    <th style="width:110px;">Enfermera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vitalSignReadings as $r)
                    <tr class="{{ Shift::pdfClass($r->shift) }}">
                        <td class="center">{{ $r->recorded_at->format('d/m/Y') }}</td>
                        <td class="center">{{ $r->recorded_at->format('H:i') }}</td>
                        <td class="center"><span class="shift-label {{ Shift::pdfClass($r->shift) }}">{{ Shift::label($r->shift) }}</span></td>
                        <td class="center">{{ $r->heart_rate ?? '—' }}</td>
                        <td class="center">{{ $r->bloodPressureFormatted() ?? '—' }}</td>
                        <td class="center">{{ $r->respiratory_rate ?? '—' }}</td>
                        <td class="center">{{ $r->temperature ? rtrim(rtrim(number_format($r->temperature, 1), '0'), '.') . '°' : '—' }}</td>
                        <td>{{ $r->notes ?: '—' }}</td>
                        <td>{{ $r->recordedBy?->fullName() ?? '—' }}@if($r->recordedBy?->professional_license) <span style="font-size:7px;">(Céd. {{ $r->recordedBy->professional_license }})</span>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- La glucemia se documenta como un registro independiente cuando fue indicada. --}}
@if($stay->glucoseMonitoringOrders->isNotEmpty() || $glucoseReadings->isNotEmpty())
<div style="margin-bottom:16px;">
    <div class="subsection-title">Lecturas de glucemia capilar ({{ $glucoseReadings->count() }})</div>
    @if($glucoseReadings->isEmpty())
        <p class="empty-note">Sin lecturas de glucemia registradas durante la estancia.</p>
    @else
        <table class="grid">
            <thead>
                <tr>
                    <th class="center" style="width:62px;">Fecha</th>
                    <th class="center" style="width:42px;">Hora</th>
                    <th class="center" style="width:54px;">Turno</th>
                    <th class="center" style="width:75px;">Glucemia</th>
                    <th>Notas</th>
                    <th style="width:110px;">Enfermera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($glucoseReadings as $reading)
                    <tr class="{{ Shift::pdfClass($reading->shift) }}">
                        <td class="center">{{ $reading->recorded_at->format('d/m/Y') }}</td>
                        <td class="center">{{ $reading->recorded_at->format('H:i') }}</td>
                        <td class="center"><span class="shift-label {{ Shift::pdfClass($reading->shift) }}">{{ Shift::label($reading->shift) }}</span></td>
                        <td class="center">{{ $reading->value_mg_dl }} mg/dL</td>
                        <td>{{ $reading->notes ?: '—' }}</td>
                        <td>{{ $reading->recordedBy?->fullName() ?? '—' }}@if($reading->recordedBy?->professional_license) <span style="font-size:7px;">(Céd. {{ $reading->recordedBy->professional_license }})</span>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endif

{{-- Resúmenes de turno: solo los que tienen datos --}}
<div style="margin-bottom:16px;">
    <div class="subsection-title">Resúmenes de turno ({{ $shiftSummaries->count() }})</div>
    @if($shiftSummaries->isEmpty())
        <p class="empty-note">Sin resúmenes de turno registrados.</p>
    @else
        @foreach($shiftSummaries as $summary)
            <div style="page-break-inside:avoid; margin-bottom:10px; border:1px solid #ccc; padding:6px;">
                <div class="shift-heading {{ Shift::pdfClass($summary->shift) }}" style="padding:3px 6px; font-weight:bold; font-size:10px; margin:-6px -6px 6px -6px;">
                    <span class="shift-label {{ Shift::pdfClass($summary->shift) }}">{{ Shift::label($summary->shift) }}</span>
                    — {{ $summary->shift_date->format('d/m/Y') }}
                </div>
                <table style="width:100%; border-collapse:collapse; font-size:9px;">
                    <tr>
                        <td style="padding:2px 4px; width:25%;"><strong>Dieta:</strong></td>
                        <td style="padding:2px 4px; width:25%;">{{ $summary->diet ?: '—' }}</td>
                        <td style="padding:2px 4px; width:25%;"><strong>Fórmula:</strong></td>
                        <td style="padding:2px 4px; width:25%;">{{ $summary->formula ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Líq. orales (ml):</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->oral_liquids_ml ?? '—' }}</td>
                        <td style="padding:2px 4px;"><strong>Líq. parenterales (ml):</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->parenteral_liquids_ml ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Uresis (ml):</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->urine_output_ml ?? '—' }}</td>
                        <td style="padding:2px 4px;"><strong>Evacuaciones:</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->evacuations_count ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Vómito (ml):</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->vomit_ml }}</td>
                        <td style="padding:2px 4px;"><strong>Aspiración (ml):</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->aspiration_ml }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Drenaje (ml):</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->drainage_ml }}</td>
                        <td style="padding:2px 4px;"><strong>Tipo de drenaje:</strong></td>
                        <td style="padding:2px 4px;">
                            {{ $summary->drainage_ml > 0 ? ($summary->drainage_type ?: '—') : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Electrolitos:</strong></td>
                        <td style="padding:2px 4px;" colspan="3">{{ $summary->electrolytes_blood_elements ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Laboratorios:</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->lab_biological_products ?: '—' }}</td>
                        <td style="padding:2px 4px;"><strong>Reactivos:</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->reagents ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 4px;"><strong>Estudios/operaciones:</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->studies_operations ?: '—' }}</td>
                        <td style="padding:2px 4px;"><strong>Enfermera responsable:</strong></td>
                        <td style="padding:2px 4px;">{{ $summary->recordedBy?->fullName() ?? '—' }}@if($summary->recordedBy?->professional_license) <span style="font-size:7px;">(Céd. {{ $summary->recordedBy->professional_license }})</span>@endif</td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif
</div>
