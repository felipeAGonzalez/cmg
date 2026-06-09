@extends('layouts.app')

@php
    use App\Support\Shift;

    $user    = auth()->user();
    $canEdit = ($user->isAdmin() || $user->isNurse()) && $stay->isActive();

    $currentReadings = $readings[$currentKey] ?? collect();
    $currentSummary  = $summaries[$currentKey] ?? null;

    // Claves de turnos anteriores ordenadas cronológicamente (desc).
    $shiftOrder = ['morning' => 1, 'evening' => 2, 'night' => 3];
    $historyKeys = $readings->keys()->merge($summaries->keys())->unique()
        ->reject(fn ($k) => $k === $currentKey)
        ->sortByDesc(function ($k) use ($shiftOrder) {
            [$date, $shift] = explode('_', $k);
            return $date . sprintf('%02d', $shiftOrder[$shift] ?? 0);
        })
        ->values();

    // Datos para la gráfica (últimas 30 tomas en orden cronológico).
    $chartReadings = $readings->collapse()->sortBy('recorded_at')->values();
    if ($chartReadings->count() > 30) {
        $chartReadings = $chartReadings->slice(-30)->values();
    }
@endphp

@section('content')
<div class="container py-4">

    {{-- ════════ ENCABEZADO ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-clipboard2-pulse text-primary me-2"></i>Hojas de Enfermería
                    </h4>
                    <div class="mb-1">
                        <span class="fw-semibold">{{ $stay->patient->fullName() }}</span>
                        <span class="text-muted">· {{ $stay->patient->age() }} años · Cuarto {{ $stay->room->number }}</span>
                    </div>
                    <div class="text-muted small mb-1"><i class="bi bi-clipboard2-pulse me-1"></i>{{ $stay->diagnosis }}</div>
                    <div class="text-muted small">
                        <i class="bi bi-rulers me-1"></i>
                        Talla: <strong>{{ $stay->height_cm ? rtrim(rtrim(number_format($stay->height_cm, 2), '0'), '.') . ' cm' : '—' }}</strong>
                        &nbsp;·&nbsp;
                        Peso: <strong>{{ $stay->weight_kg ? rtrim(rtrim(number_format($stay->weight_kg, 2), '0'), '.') . ' kg' : '—' }}</strong>
                        @if($user->isAdmin() || $user->isNurse())
                            <a href="{{ route('stays.measurements.edit', $stay) }}" class="ms-2"><i class="bi bi-pencil"></i> Editar</a>
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-6 mb-2">
                        <i class="bi bi-clock me-1"></i>Turno actual: {{ Shift::label($currentShift['shift']) }}
                        ({{ Shift::timeRange($currentShift['shift']) }})
                    </span>
                    <div>
                        <a href="{{ route('stays.show', ['room' => $stay->room, 'stay' => $stay->id]) }}#nursing"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Volver al paciente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- ════════ SIGNOS VITALES — TURNO ACTUAL ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bi bi-activity me-1"></i>Signos Vitales — {{ Shift::label($currentShift['shift']) }},
                {{ $currentShift['shift_date']->format('d/m/Y') }}
            </h6>
            @if($canEdit)
                <button type="button" class="btn btn-primary btn-sm" id="btnNuevaToma">
                    <i class="bi bi-plus-circle me-1"></i>Registrar nueva toma
                </button>
            @endif
        </div>
        <div class="card-body">
            @include('nursing-sheets.partials.vitals-table', [
                'rows'        => $currentReadings,
                'showActions' => $canEdit,
            ])
        </div>
    </div>

    {{-- ════════ RESUMEN DEL TURNO ACTUAL ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bi bi-card-checklist me-1"></i>Resumen del Turno actual
            </h6>
            @if($canEdit)
                <a href="{{ route('shiftSummary.edit', $stay) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>{{ $currentSummary ? 'Editar resumen' : 'Llenar resumen del turno' }}
                </a>
            @endif
        </div>
        <div class="card-body">
            @if($currentSummary)
                @include('nursing-sheets.partials.summary-detail', ['summary' => $currentSummary])
            @else
                <p class="text-muted fst-italic mb-0">Aún no se ha capturado el resumen del turno.</p>
            @endif
        </div>
    </div>

    {{-- ════════ GRÁFICA DE SIGNOS VITALES ════════ --}}
    @if($chartReadings->count() >= 2)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-graph-up me-1"></i>Gráfica de signos vitales</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><canvas id="chartHr" height="140"></canvas></div>
                <div class="col-md-6"><canvas id="chartBp" height="140"></canvas></div>
                <div class="col-md-6"><canvas id="chartRr" height="140"></canvas></div>
                <div class="col-md-6"><canvas id="chartTemp" height="140"></canvas></div>
            </div>
        </div>
    </div>
    @endif

    {{-- ════════ ADMINISTRACIONES DE MEDICAMENTOS ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bi bi-clipboard-check me-1"></i>Administraciones de medicamentos
                <span class="text-muted fw-normal">({{ $administrationsTotal }} en total)</span>
            </h6>
            <a href="{{ route('medicationAdministrations.index', $stay) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-list-ul me-1"></i>Ver todas
            </a>
        </div>
        <div class="card-body">
            @if($recentAdministrations->isEmpty())
                <p class="text-muted fst-italic mb-0">Sin administraciones registradas aún.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Hora</th><th>Medicamento</th><th>Dosis</th><th>Estado</th><th>Enfermera</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentAdministrations as $a)
                            <tr>
                                <td class="text-nowrap small">{{ $a->administered_at->format('d/m H:i') }}</td>
                                <td>{{ $a->medicationOrder->medication_name }}</td>
                                <td>{{ $a->actual_dose }}</td>
                                <td><span class="badge {{ $a->statusBadgeClass() }}">{{ $a->statusLabel() }}</span></td>
                                <td class="text-muted small">{{ $a->recordedBy?->fullName() ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════ NOTAS Y REGISTROS DE ENFERMERÍA ════════ --}}
    @php
        $recentEntries = \App\Models\NursingEntry::forStay($stay->id)
            ->with('recordedBy')
            ->limit(10)
            ->get();
    @endphp
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bi bi-journal-text me-1"></i>Notas y registros de enfermería
            </h6>
            @if($user->isAdmin() || $user->isNurse())
                <a href="{{ route('nursingEntries.create', $stay) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo registro
                </a>
            @endif
        </div>
        <div class="card-body">
            @if($recentEntries->isEmpty())
                <p class="text-muted fst-italic mb-0">Aún no se han registrado notas.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Hora</th><th>Categoría</th><th>Descripción</th><th>Enfermera</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentEntries as $entry)
                            <tr>
                                <td class="text-nowrap small">{{ $entry->recorded_at->format('d/m H:i') }}</td>
                                <td>
                                    <span class="badge {{ $entry->categoryBadgeClass() }}">
                                        <i class="bi {{ $entry->categoryIcon() }} me-1"></i>{{ $entry->categoryLabel() }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($entry->description, 80) }}</td>
                                <td class="text-muted small">{{ $entry->recordedBy?->fullName() ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @if($entry->isEditable() && ($user->isAdmin() || $user->isNurse()))
                                        <a href="{{ route('nursingEntries.edit', $entry) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('nursingEntries.destroy', $entry) }}"
                                              class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <a href="{{ route('nursingEntries.index', $stay) }}" class="btn btn-sm btn-link px-0">
                        Ver todas las notas y registros →
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════ HISTORIAL — TURNOS ANTERIORES ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-clock-history me-1"></i>Historial — Turnos anteriores</h6>
        </div>
        <div class="card-body">
            @if($historyKeys->isEmpty())
                <p class="text-muted fst-italic mb-0">Sin registros de turnos anteriores.</p>
            @else
                <div class="accordion" id="historyAccordion">
                    @foreach($historyKeys as $i => $key)
                        @php
                            [$dateStr, $shiftCode] = explode('_', $key);
                            $shiftDate    = \Carbon\Carbon::createFromFormat('Y-m-d', $dateStr);
                            $rows         = $readings[$key] ?? collect();
                            $summary      = $summaries[$key] ?? null;
                            $editor       = $summary?->recordedBy?->fullName()
                                            ?? optional($rows->last())->recordedBy?->fullName();
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#hist{{ $i }}">
                                    <span>
                                        <strong>{{ Shift::label($shiftCode) }}</strong> ·
                                        {{ $shiftDate->format('d/m/Y') }}
                                        <span class="text-muted">({{ Shift::timeRange($shiftCode) }})</span>
                                        @if($editor)
                                            <span class="text-muted small">— última edición: {{ $editor }}</span>
                                        @endif
                                    </span>
                                </button>
                            </h2>
                            <div id="hist{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                                <div class="accordion-body">
                                    <div class="fw-semibold small text-muted mb-1">Signos vitales</div>
                                    @include('nursing-sheets.partials.vitals-table', [
                                        'rows'        => $rows,
                                        'showActions' => false,
                                    ])

                                    <div class="fw-semibold small text-muted mt-3 mb-1">Resumen del turno</div>
                                    @if($summary)
                                        @include('nursing-sheets.partials.summary-detail', ['summary' => $summary])
                                    @else
                                        <p class="text-muted fst-italic mb-0">Sin resumen de turno capturado.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>{{-- /container --}}

@if($canEdit)
{{-- Modal: registrar nueva toma --}}
<div class="modal fade" id="nuevaTomaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form method="POST" action="{{ route('vitalSigns.store', $stay) }}">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-activity me-1"></i>Registrar nueva toma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('nursing-sheets.partials.vitals-fields')
                    <div class="form-text">Captura al menos un signo vital.</div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Registrar toma</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: editar toma --}}
<div class="modal fade" id="editarTomaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form method="POST" id="editarTomaForm">
                @csrf
                @method('PUT')
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i>Editar toma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('nursing-sheets.partials.vitals-fields', ['prefix' => 'edit_'])
                    <div class="form-text">Captura al menos un signo vital.</div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Form oculto para eliminar --}}
<form method="POST" id="deleteReadingForm" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@push('scripts')
@if($chartReadings->count() >= 2)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {

    @if($canEdit)
    const openModal = (id) => {
        const el = document.getElementById(id);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    };

    const btnNueva = document.getElementById('btnNuevaToma');
    if (btnNueva) btnNueva.addEventListener('click', () => openModal('nuevaTomaModal'));

    // Reabrir el modal de captura si hubo errores de validación.
    @if($errors->any())
        openModal('nuevaTomaModal');
    @endif

    // Editar toma: precargar datos y apuntar el form a la ruta correcta.
    const editForm = document.getElementById('editarTomaForm');
    document.querySelectorAll('.btn-edit-reading').forEach(function (btn) {
        btn.addEventListener('click', function () {
            editForm.action = '{{ url('vital-signs') }}/' + btn.dataset.id;
            document.getElementById('edit_heart_rate').value = btn.dataset.heart_rate || '';
            document.getElementById('edit_blood_pressure_systolic').value = btn.dataset.blood_pressure_systolic || '';
            document.getElementById('edit_blood_pressure_diastolic').value = btn.dataset.blood_pressure_diastolic || '';
            document.getElementById('edit_respiratory_rate').value = btn.dataset.respiratory_rate || '';
            document.getElementById('edit_temperature').value = btn.dataset.temperature || '';
            document.getElementById('edit_notes').value = btn.dataset.notes || '';
            openModal('editarTomaModal');
        });
    });

    // Eliminar toma con confirmación.
    const delForm = document.getElementById('deleteReadingForm');
    document.querySelectorAll('.btn-delete-reading').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (confirm('¿Eliminar esta toma de signos vitales?')) {
                delForm.action = btn.dataset.action;
                delForm.submit();
            }
        });
    });
    @endif

    @if($chartReadings->count() >= 2)
    const labels = @json($chartReadings->map(fn ($r) => \Carbon\Carbon::parse($r->recorded_at)->format('d/m H:i')));
    const mkChart = (id, label, color, data) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ label: label, data: data, borderColor: color, backgroundColor: color, tension: .3, spanGaps: true }] },
            options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: false } } }
        });
    };

    mkChart('chartHr', 'F.C. (lpm)', '#e53935', @json($chartReadings->map(fn ($r) => $r->heart_rate)));
    mkChart('chartRr', 'F.R. (rpm)', '#43a047', @json($chartReadings->map(fn ($r) => $r->respiratory_rate)));
    mkChart('chartTemp', 'Temp (°C)', '#fb8c00', @json($chartReadings->map(fn ($r) => $r->temperature !== null ? (float) $r->temperature : null)));

    const bpCtx = document.getElementById('chartBp');
    if (bpCtx) {
        new Chart(bpCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'T.A. sistólica', data: @json($chartReadings->map(fn ($r) => $r->blood_pressure_systolic)), borderColor: '#1e88e5', backgroundColor: '#1e88e5', tension: .3, spanGaps: true },
                    { label: 'T.A. diastólica', data: @json($chartReadings->map(fn ($r) => $r->blood_pressure_diastolic)), borderColor: '#90caf9', backgroundColor: '#90caf9', tension: .3, spanGaps: true }
                ]
            },
            options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: false } } }
        });
    }
    @endif
});
</script>
@endpush
