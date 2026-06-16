@php
    use App\Support\Shift;

    $hasGlucoseOrders = $stay->glucoseMonitoringOrders->isNotEmpty();
    $glucoseReadingsByTimestamp = $stay->glucoseReadings
        ->keyBy(fn ($g) => $g->recorded_at->format('Y-m-d H:i:s'));
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
                    <th class="center" style="width:42px;">F.C.</th>
                    <th class="center" style="width:58px;">T.A.</th>
                    <th class="center" style="width:42px;">F.R.</th>
                    <th class="center" style="width:48px;">Temp.</th>
                    @if($hasGlucoseOrders)<th class="center" style="width:42px;">Gluc.</th>@endif
                    <th>Notas</th>
                    <th style="width:110px;">Enfermera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vitalSignReadings as $r)
                    @php $glucose = $glucoseReadingsByTimestamp[$r->recorded_at->format('Y-m-d H:i:s')] ?? null; @endphp
                    <tr>
                        <td class="center">{{ $r->recorded_at->format('d/m/Y') }}</td>
                        <td class="center">{{ $r->recorded_at->format('H:i') }}</td>
                        <td class="center">{{ $r->heart_rate ?? '—' }}</td>
                        <td class="center">{{ $r->bloodPressureFormatted() ?? '—' }}</td>
                        <td class="center">{{ $r->respiratory_rate ?? '—' }}</td>
                        <td class="center">{{ $r->temperature ? rtrim(rtrim(number_format($r->temperature, 1), '0'), '.') . '°' : '—' }}</td>
                        @if($hasGlucoseOrders)<td class="center">{{ $glucose?->value_mg_dl ?? '—' }}</td>@endif
                        <td>{{ $r->notes ?: '—' }}</td>
                        <td>{{ $r->recordedBy?->fullName() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Resúmenes de turno: solo los que tienen datos --}}
<div style="margin-bottom:16px;">
    <div class="subsection-title">Resúmenes de turno ({{ $shiftSummaries->count() }})</div>
    @if($shiftSummaries->isEmpty())
        <p class="empty-note">Sin resúmenes de turno registrados.</p>
    @else
        @foreach($shiftSummaries as $summary)
            <div style="page-break-inside:avoid; margin-bottom:10px; border:1px solid #ccc; padding:6px;">
                <div style="background:#E3F2FD; padding:3px 6px; font-weight:bold; font-size:10px; margin:-6px -6px 6px -6px;">
                    {{ Shift::label($summary->shift) }} — {{ $summary->shift_date->format('d/m/Y') }}
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
                        <td style="padding:2px 4px;">{{ $summary->recordedBy?->fullName() ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif
</div>
