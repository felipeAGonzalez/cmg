{{--
    Tabla de lecturas de signos vitales.
    Variables esperadas:
      $rows        : Collection<VitalSignReading>
      $showActions : bool  (mostrar columna Acciones con Editar/Eliminar)
    Usa $stay (del scope padre) para la columna condicional de glucemia.
--}}
@php
    $hasGlucoseOrders = $stay->glucoseMonitoringOrders->isNotEmpty();
    $glucoseReadingsByTimestamp = $stay->glucoseReadings
        ->keyBy(fn ($g) => $g->recorded_at->format('Y-m-d H:i:s'));
    $baseCols = $hasGlucoseOrders ? 8 : 7;
@endphp
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Hora</th>
                <th>F.C.</th>
                <th>T.A.</th>
                <th>F.R.</th>
                <th>Temp</th>
                @if($hasGlucoseOrders)<th>Glucemia (mg/dL)</th>@endif
                <th>Notas</th>
                <th>Enfermera</th>
                @if($showActions)<th class="text-end">Acciones</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            @php $glucose = $glucoseReadingsByTimestamp[$r->recorded_at->format('Y-m-d H:i:s')] ?? null; @endphp
            <tr>
                <td class="text-nowrap">{{ $r->recorded_at->format('H:i') }}</td>
                <td>{{ $r->heart_rate ?? '—' }}</td>
                <td>{{ $r->bloodPressureFormatted() ?? '—' }}</td>
                <td>{{ $r->respiratory_rate ?? '—' }}</td>
                <td>{{ $r->temperature !== null ? rtrim(rtrim(number_format($r->temperature, 2), '0'), '.') . '°' : '—' }}</td>
                @if($hasGlucoseOrders)
                <td>
                    @if($glucose)
                        <span class="badge {{ $glucose->rangeBadgeClass() }}">{{ $glucose->value_mg_dl }}</span>
                    @else
                        —
                    @endif
                </td>
                @endif
                <td>{{ $r->notes ?? '—' }}</td>
                <td class="text-muted small">{{ $r->recordedBy?->fullName() ?? '—' }}</td>
                @if($showActions)
                <td class="text-end text-nowrap">
                    @if($r->isEditable())
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-edit-reading"
                                data-id="{{ $r->id }}"
                                data-heart_rate="{{ $r->heart_rate }}"
                                data-blood_pressure_systolic="{{ $r->blood_pressure_systolic }}"
                                data-blood_pressure_diastolic="{{ $r->blood_pressure_diastolic }}"
                                data-respiratory_rate="{{ $r->respiratory_rate }}"
                                data-temperature="{{ $r->temperature }}"
                                data-notes="{{ $r->notes }}"
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete-reading"
                                data-action="{{ route('vitalSigns.destroy', $r) }}"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $showActions ? $baseCols + 1 : $baseCols }}" class="text-center text-muted fst-italic py-3">
                    Sin tomas registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
