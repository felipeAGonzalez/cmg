@php
    use Carbon\Carbon;

    $statusColors = [
        'active'    => '#2E7D32',
        'suspended' => '#F57C00',
        'finished'  => '#757575',
    ];

    // Encabezado reutilizable de la tabla de administraciones.
    $admHead = '<thead><tr style="background:#F5F5F5;">'
        . '<th style="border:1px solid #999; padding:3px;">Fecha y hora</th>'
        . '<th style="border:1px solid #999; padding:3px;">Dosis real</th>'
        . '<th style="border:1px solid #999; padding:3px;">Estado</th>'
        . '<th style="border:1px solid #999; padding:3px;">Motivo</th>'
        . '<th style="border:1px solid #999; padding:3px;">Observaciones</th>'
        . '<th style="border:1px solid #999; padding:3px;">Enfermera</th>'
        . '</tr></thead>';
@endphp

<div class="chapter-title">Hoja 2 — Medicamentos y notas de enfermería</div>

{{-- ─────────── Medicamentos ─────────── --}}
<div class="subsection-title">Medicamentos prescritos ({{ $medicationOrders->count() }})</div>

@if($medicationOrders->isEmpty())
    <p class="empty-note">Sin prescripciones de medicamentos durante la estancia.</p>
@else
    @foreach($medicationOrders as $order)
        @php
            $administrations = $order->administrations->sortBy('administered_at');
            $useCompactCard = $administrations->count() > 8;
            $color = $statusColors[$order->status()] ?? '#757575';
        @endphp

        @if(! $useCompactCard)
            {{-- Card grande con tabla anidada (≤ 8 administraciones) --}}
            <div style="page-break-inside:avoid; margin-bottom:14px; border:1px solid #ccc; padding:8px;">
                <div style="background:#E3F2FD; padding:4px 8px; margin:-8px -8px 8px -8px;">
                    <strong style="font-size:11px;">{{ $order->medication_name }}@if($order->dose) — {{ $order->dose }}@endif</strong>
                    <span style="float:right; font-size:9px;">
                        <span style="background:{{ $color }}; color:#fff; padding:2px 6px; border-radius:3px;">{{ $order->statusLabel() }}</span>
                    </span>
                </div>

                <table style="width:100%; font-size:9px; border-collapse:collapse;">
                    <tr>
                        <td style="padding:2px 4px; width:50%;"><strong>Vía:</strong> {{ $order->routeLabel() }} · <strong>Frecuencia:</strong> {{ $order->frequencyLabel() }}</td>
                        <td style="padding:2px 4px; width:50%;"><strong>Inicio:</strong> {{ $order->start_date->format('d/m/Y') }}@if($order->duration_days) · <strong>Duración:</strong> {{ $order->duration_days }} día(s)@endif</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:2px 4px;"><strong>Prescrita por:</strong> Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}@if($order->prescribedBy?->professional_license) (Céd. {{ $order->prescribedBy->professional_license }})@endif · <strong>Capturada por:</strong> {{ $order->createdBy?->fullName() ?? '—' }}</td>
                    </tr>
                    @if($order->indications)
                        <tr><td colspan="2" style="padding:2px 4px;"><strong>Indicaciones:</strong> {{ $order->indications }}</td></tr>
                    @endif
                    @if($order->isSuspended())
                        <tr><td colspan="2" style="padding:2px 4px; color:#F57C00;"><strong>Suspendida</strong> el {{ Carbon::parse($order->suspended_at)->format('d/m/Y H:i') }} por {{ $order->suspendedBy?->fullName() ?? '—' }}@if($order->suspension_reason) — {{ $order->suspension_reason }}@endif</td></tr>
                    @endif
                </table>

                <div style="margin-top:6px;">
                    <strong style="font-size:9px;">Administraciones ({{ $administrations->count() }}):</strong>
                    @if($administrations->isEmpty())
                        <p class="empty-note" style="margin:4px 0;">Sin administraciones registradas.</p>
                    @else
                        <table style="width:100%; border-collapse:collapse; font-size:8px; margin-top:4px;">
                            {!! $admHead !!}
                            <tbody>
                                @foreach($administrations as $a)
                                    <tr>
                                        <td style="border:1px solid #999; padding:3px;">{{ Carbon::parse($a->administered_at)->format('d/m/Y H:i') }}</td>
                                        <td style="border:1px solid #999; padding:3px;">{{ $a->actual_dose ?: '—' }}</td>
                                        <td style="border:1px solid #999; padding:3px;">{{ $a->statusLabel() }}</td>
                                        <td style="border:1px solid #999; padding:3px;">{{ $a->reason ?: '—' }}</td>
                                        <td style="border:1px solid #999; padding:3px;">{{ $a->observations ?: '—' }}</td>
                                        <td style="border:1px solid #999; padding:3px;">{{ $a->recordedBy?->fullName() ?? '—' }}@if($a->recordedBy?->professional_license) <span style="font-size:7px;">(Céd. {{ $a->recordedBy->professional_license }})</span>@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @else
            {{-- Card compacta (> 8 administraciones): encabezado + tabla aparte --}}
            <div style="margin-bottom:6px; border:1px solid #ccc; page-break-inside:avoid;">
                <div style="background:#E3F2FD; padding:4px 8px;">
                    <strong style="font-size:11px;">{{ $order->medication_name }}@if($order->dose) — {{ $order->dose }}@endif</strong>
                    <span style="float:right; font-size:9px;">{{ $order->statusLabel() }} · {{ $administrations->count() }} administraciones</span>
                </div>
                <div style="padding:4px 8px; font-size:9px; background:#FAFAFA;">
                    {{ $order->routeLabel() }} · {{ $order->frequencyLabel() }} · Inicio {{ $order->start_date->format('d/m/Y') }}@if($order->duration_days) · {{ $order->duration_days }} día(s)@endif · Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}@if($order->prescribedBy?->professional_license) (Céd. {{ $order->prescribedBy->professional_license }})@endif
                    @if($order->indications)
                        <br><em>{{ $order->indications }}</em>
                    @endif
                    @if($order->isSuspended())
                        <br><span style="color:#F57C00;">Suspendida {{ Carbon::parse($order->suspended_at)->format('d/m/Y') }}@if($order->suspension_reason): {{ $order->suspension_reason }}@endif</span>
                    @endif
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <strong style="font-size:9px;">Administraciones de {{ $order->medication_name }}:</strong>
                <table style="width:100%; border-collapse:collapse; font-size:8px; margin-top:4px;">
                    {!! $admHead !!}
                    <tbody>
                        @foreach($administrations as $a)
                            <tr>
                                <td style="border:1px solid #999; padding:3px;">{{ Carbon::parse($a->administered_at)->format('d/m/Y H:i') }}</td>
                                <td style="border:1px solid #999; padding:3px;">{{ $a->actual_dose ?: '—' }}</td>
                                <td style="border:1px solid #999; padding:3px;">{{ $a->statusLabel() }}</td>
                                <td style="border:1px solid #999; padding:3px;">{{ $a->reason ?: '—' }}</td>
                                <td style="border:1px solid #999; padding:3px;">{{ $a->observations ?: '—' }}</td>
                                <td style="border:1px solid #999; padding:3px;">{{ $a->recordedBy?->fullName() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
@endif

{{-- ─────────── Notas de enfermería ─────────── --}}
<div class="subsection-title" style="margin-top:16px;">Notas de enfermería</div>

@if($nursingEntriesByDay->isEmpty())
    <p class="empty-note">Sin notas de enfermería registradas durante la estancia.</p>
@else
    @foreach($nursingEntriesByDay as $dayKey => $entries)
        @php $dayCarbon = Carbon::parse($dayKey); @endphp
        <div style="page-break-inside:avoid; margin-bottom:10px;">
            <div class="day-header">
                {{ $dayCarbon->format('d/m/Y') }} ({{ $entries->count() }} {{ $entries->count() === 1 ? 'nota' : 'notas' }})
            </div>
            <table class="grid">
                <thead>
                    <tr>
                        <th class="center" style="width:42px;">Hora</th>
                        <th style="width:120px;">Categoría</th>
                        <th>Descripción</th>
                        <th style="width:110px;">Enfermera</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td class="center">{{ $entry->recorded_at->format('H:i') }}</td>
                            <td>{{ $entry->categoryLabel() }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ $entry->recordedBy?->fullName() ?? '—' }}@if($entry->recordedBy?->professional_license) <span style="font-size:7px;">(Céd. {{ $entry->recordedBy->professional_license }})</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endif
