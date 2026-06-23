@php
    use Carbon\Carbon;

    $fbStatusBadge = [
        'active'     => 'badge-green',
        'suspended'  => 'badge-yellow',
        'discharged' => 'badge-gray',
    ];

    $fmtSigned = fn ($n) => ($n > 0 ? '+' : '') . number_format($n);
@endphp

<div class="chapter-title">Hojas 3-4 — Balance de líquidos en 24 horas</div>

@foreach($stay->fluidBalanceOrders as $order)
    <table class="grid" style="margin-bottom:6px;">
        <tr>
            <td>
                <strong style="font-size:10px;">Orden de balance — Inicio {{ $order->start_date?->format('d/m/Y') ?? '—' }}</strong>
                <span class="badge {{ $fbStatusBadge[$order->status()] ?? 'badge-gray' }}" style="float:right;">{{ $order->statusLabel() }}</span>
                <br>
                <span style="font-size:8.5px; color:#444;">
                    Prescrita por Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}@if($order->prescribedBy?->professional_license) (Céd. {{ $order->prescribedBy->professional_license }})@endif
                    @if($order->clinical_reason)
                        <br>Motivo clínico: {{ $order->clinical_reason }}
                    @endif
                    @if($order->suspended_at && ! $order->isDischargedReason())
                        <br><span style="color:#C62828;">Suspendida el {{ Carbon::parse($order->suspended_at)->format('d/m/Y H:i') }}@if($order->suspension_reason) — {{ $order->suspension_reason }}@endif</span>
                    @elseif($order->isDischargedReason())
                        <br><span style="color:#666;">Finalizada por egreso el {{ Carbon::parse($order->suspended_at)->format('d/m/Y H:i') }}.</span>
                    @endif
                </span>
            </td>
        </tr>
    </table>

    @forelse($order->days as $day)
        <div style="page-break-inside:avoid; margin-bottom:6px;">
        <div class="day-header">
            Día {{ $day->day_number }}
            <span class="sub">({{ Carbon::parse($day->start_at)->format('d/m/Y H:i') }} a {{ Carbon::parse($day->end_at)->format('d/m/Y H:i') }})</span>
        </div>

        {{-- Resumen del día (barra compacta) --}}
        <div class="summary-box">
            Ingresos: <span class="val">{{ number_format($day->total_inputs_ml) }} ml</span> ·
            Egresos medibles: <span class="val">{{ number_format($day->total_measured_outputs_ml) }} ml</span> ·
            Insensibles: <span class="val">{{ number_format($day->total_insensible_losses_ml) }} ml</span> ·
            Balance neto: <span class="val">{{ $fmtSigned($day->net_balance_ml) }} ml</span>
        </div>

        @if($day->entries->isNotEmpty())
            <table class="grid fb-table">
                <thead>
                    <tr>
                        <th class="center">Hora</th>
                        <th class="center">Oral</th>
                        <th class="center">IV</th>
                        <th class="center">Sangre</th>
                        <th class="center">Plasma</th>
                        <th class="center">Sonda</th>
                        <th class="center">Otros</th>
                        <th class="center">Orina</th>
                        <th class="center">Evac.</th>
                        <th class="center">Vómito</th>
                        <th class="center">Hemo.</th>
                        <th class="center">Succ.</th>
                        <th class="center">Canal.</th>
                        <th class="center">Insens.</th>
                        <th class="center">Σ Ing.</th>
                        <th class="center">Σ Egr.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($day->entries as $e)
                        <tr>
                            <td class="center">{{ Carbon::parse($e->recorded_at)->format('H:i') }}</td>
                            <td class="num">{{ $e->oral_ml }}</td>
                            <td class="num">{{ $e->iv_solution_ml }}</td>
                            <td class="num">{{ $e->blood_ml }}</td>
                            <td class="num">{{ $e->plasma_ml }}</td>
                            <td class="num">{{ $e->sonda_ml }}</td>
                            <td class="num">{{ $e->other_inputs_ml }}</td>
                            <td class="num">{{ $e->urine_ml }}</td>
                            <td class="num">{{ $e->evacuation_ml }}</td>
                            <td class="num">{{ $e->vomit_ml }}</td>
                            <td class="num">{{ $e->hemorrhage_ml }}</td>
                            <td class="num">{{ $e->suction_ml }}</td>
                            <td class="num">{{ $e->canalization_ml }}</td>
                            <td class="num">{{ $e->insensible_losses_ml }}</td>
                            <td class="num"><strong>{{ $e->totalInputs() }}</strong></td>
                            <td class="num"><strong>{{ $e->totalOutputs() }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="center">Totales</th>
                        <th colspan="6"></th>
                        <th colspan="6"></th>
                        <th class="num">{{ number_format($day->total_inputs_ml) }}</th>
                        <th class="num">{{ number_format($day->total_measured_outputs_ml + $day->total_insensible_losses_ml) }}</th>
                    </tr>
                </tfoot>
            </table>

            {{-- Observaciones de las tomas del día --}}
            @php
                $withObs = $day->entries->filter(fn ($e) => filled($e->observation));
            @endphp
            @if($withObs->isNotEmpty())
                <div style="font-size:8.5px; margin:2px 0 8px;">
                    <strong>Observaciones del día:</strong>
                    <ul style="margin:2px 0 0; padding-left:16px;">
                        @foreach($withObs as $e)
                            <li>{{ Carbon::parse($e->recorded_at)->format('H:i') }} — {{ $e->observation }}
                                <span class="muted">({{ $e->recordedBy?->fullName() ?? '—' }}@if($e->recordedBy?->professional_license) — Céd. {{ $e->recordedBy->professional_license }}@endif)</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <p class="empty-note">Sin tomas registradas para este día.</p>
        @endif
        </div>
    @empty
        <p class="empty-note">Sin tomas registradas para esta orden.</p>
    @endforelse
@endforeach
