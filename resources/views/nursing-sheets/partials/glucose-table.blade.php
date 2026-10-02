{{--
    Tabla independiente de lecturas de glucemia capilar.
    Variables esperadas:
      $rows        : Collection<GlucoseReading>
      $showActions : bool
--}}
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Hora</th>
                <th>Glucemia</th>
                <th>Notas</th>
                <th>Enfermera</th>
                @if($showActions)<th class="text-end">Acciones</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $reading)
            <tr class="{{ \App\Support\Shift::tableClass($reading->shift) }}">
                <td class="text-nowrap">{{ $reading->recorded_at->format('H:i') }}</td>
                <td>
                    <span class="badge {{ $reading->rangeBadgeClass() }}">
                        {{ $reading->value_mg_dl }} mg/dL
                    </span>
                </td>
                <td>{{ $reading->notes ?? '—' }}</td>
                <td class="small">
                    <span class="badge {{ \App\Support\Shift::badgeClass($reading->shift) }} mb-1">
                        {{ \App\Support\Shift::label($reading->shift) }}
                    </span><br>
                    {{ $reading->recordedBy?->fullName() ?? '—' }}
                </td>
                @if($showActions)
                <td class="text-end text-nowrap">
                    @if($reading->isEditable())
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-edit-glucose"
                                data-id="{{ $reading->id }}"
                                data-value_mg_dl="{{ $reading->value_mg_dl }}"
                                data-notes="{{ $reading->notes }}"
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete-glucose"
                                data-action="{{ route('glucoseReadings.destroy', $reading) }}"
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
                <td colspan="{{ $showActions ? 5 : 4 }}" class="text-center text-muted fst-italic py-3">
                    Sin lecturas de glucemia registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
