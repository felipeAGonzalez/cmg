@extends('layouts.app')

@php
    use App\Support\Shift;

    $user       = auth()->user();
    $canCapture = $order->isActive() && ($user->isAdmin() || $user->isNurse());

    $inputCols = [
        'oral_ml' => 'Oral', 'iv_solution_ml' => 'IV', 'blood_ml' => 'Sangre',
        'plasma_ml' => 'Plasma', 'sonda_ml' => 'Sonda', 'other_inputs_ml' => 'Otros',
    ];
    $outputCols = [
        'urine_ml' => 'Orina', 'evacuation_ml' => 'Evac', 'vomit_ml' => 'Vómito',
        'hemorrhage_ml' => 'Hemo', 'suction_ml' => 'Asp', 'canalization_ml' => 'Canal',
    ];
@endphp

@section('content')
<div class="container-fluid py-4" style="max-width:1400px;">

    {{-- ════════ ENCABEZADO ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-droplet-half text-primary me-2"></i>Balance de líquidos</h4>
                <div class="mb-1">
                    <span class="fw-semibold">{{ $stay->patient->fullName() }}</span>
                    <span class="text-muted">· {{ $stay->patient->age() }} años · Cuarto {{ $stay->room->number }}</span>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-rulers me-1"></i>
                    Talla: <strong>{{ rtrim(rtrim(number_format($stay->height_cm, 2), '0'), '.') }} cm</strong>
                    &nbsp;·&nbsp;
                    Peso: <strong>{{ rtrim(rtrim(number_format($stay->weight_kg, 2), '0'), '.') }} kg</strong>
                </div>
            </div>
            <div class="text-end">
                <span class="badge {{ $order->statusBadgeClass() }} fs-6 mb-2">{{ $order->statusLabel() }}</span>
                <div>
                    <a href="{{ route('medicationOrders.index', $stay) }}#other-indications-tab"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Indicaciones
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Información de la orden --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body row g-2 small">
            <div class="col-md-4"><span class="text-muted">Inicio del balance:</span> <strong>{{ $order->start_date->format('d/m/Y') }}</strong></div>
            <div class="col-md-4"><span class="text-muted">Médico prescriptor:</span> Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}</div>
            @if($order->clinical_reason)
                <div class="col-12"><span class="text-muted">Motivo clínico:</span> {{ $order->clinical_reason }}</div>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($order->days->isEmpty())
        {{-- ════════ VISTA VACÍA ════════ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-droplet text-muted" style="font-size:2.5rem;"></i>
                <p class="text-muted mt-2 mb-3">Aún no se han registrado tomas de balance.</p>
                @if($canCapture)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newEntryModal">
                        <i class="bi bi-plus-circle me-1"></i>Registrar primera toma
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- ════════ DÍAS (scroll vertical, ascendente) ════════ --}}
    @foreach($order->days as $day)
        @php
            $entries = $day->entries;
            $isClosed  = $day->isClosed();
            $isExpired = $day->isExpired();
            $hoursLeft = $isClosed ? 0 : max(0, now()->diffInHours($day->end_at, false));
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0 d-inline">DÍA {{ $day->day_number }}</h5>
                    <span class="text-muted ms-2 small">
                        {{ $day->start_at->format('d/m/Y H:i') }} a {{ $day->end_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div>
                    @if($isClosed)
                        <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Cerrado</span>
                    @elseif($isExpired)
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Cumplió 24h, se cerrará automáticamente</span>
                    @else
                        <span class="badge bg-info text-dark"><i class="bi bi-hourglass-split me-1"></i>En curso · {{ $hoursLeft }}h restantes</span>
                    @endif
                </div>
            </div>
            <div class="card-body">

                {{-- Resumen del día --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="small text-muted">Total Ingresos</div>
                            <div class="fw-bold text-success fs-5">{{ number_format($day->total_inputs_ml) }} ml</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="small text-muted">Egresos Medibles</div>
                            <div class="fw-bold text-danger fs-5">{{ number_format($day->total_measured_outputs_ml) }} ml</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="small text-muted">Pérdidas Insensibles</div>
                            <div class="fw-bold text-secondary fs-5">{{ number_format($day->total_insensible_losses_ml) }} ml</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="small text-muted">Balance Neto</div>
                            <div class="fw-bold fs-5 {{ $day->net_balance_ml >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ $day->net_balance_ml >= 0 ? '+' : '' }}{{ number_format($day->net_balance_ml) }} ml
                            </div>
                        </div>
                    </div>
                </div>

                @if($canCapture && ! $isClosed)
                    <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#newEntryModal">
                        <i class="bi bi-plus-circle me-1"></i>Registrar nueva toma
                    </button>
                @endif

                {{-- Tabla de tomas --}}
                <div class="fw-semibold small text-muted mb-1">Tomas registradas ({{ $entries->count() }})</div>
                @if($entries->isEmpty())
                    <p class="text-muted fst-italic mb-0">Sin tomas en este día.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size:.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2" class="align-middle">Hora</th>
                                    <th colspan="{{ count($inputCols) + 1 }}" class="text-success">Ingresos</th>
                                    <th colspan="{{ count($outputCols) + 1 }}" class="text-danger">Egresos medibles</th>
                                    <th rowspan="2" class="align-middle text-secondary">Resp/<br>Sudor</th>
                                    <th rowspan="2" class="align-middle">Balance</th>
                                    <th rowspan="2" class="align-middle">Obs</th>
                                    <th rowspan="2" class="align-middle">Enfermera</th>
                                    @if($canCapture)<th rowspan="2" class="align-middle">Acciones</th>@endif
                                </tr>
                                <tr>
                                    @foreach($inputCols as $label)<th class="text-success">{{ $label }}</th>@endforeach
                                    <th class="text-success">Subt.</th>
                                    @foreach($outputCols as $label)<th class="text-danger">{{ $label }}</th>@endforeach
                                    <th class="text-danger">Subt.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $entry)
                                    <tr>
                                        <td class="text-nowrap">{{ $entry->recorded_at->format('H:i') }}</td>
                                        @foreach($inputCols as $field => $label)<td>{{ $entry->$field ?: '' }}</td>@endforeach
                                        <td class="fw-semibold text-success">{{ $entry->totalInputs() ?: '' }}</td>
                                        @foreach($outputCols as $field => $label)<td>{{ $entry->$field ?: '' }}</td>@endforeach
                                        <td class="fw-semibold text-danger">{{ $entry->totalMeasuredOutputs() ?: '' }}</td>
                                        <td class="text-secondary bg-light"
                                            title="{{ $entry->formulaLabel() }} · {{ $entry->hours_since_previous }}h · {{ $entry->temperature_at_entry ? $entry->temperature_at_entry.'°C' : 'sin temp' }}">
                                            {{ $entry->insensible_losses_ml ?: '' }}
                                        </td>
                                        <td class="fw-semibold {{ $entry->netBalance() >= 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ $entry->netBalance() >= 0 ? '+' : '' }}{{ $entry->netBalance() }}
                                        </td>
                                        <td class="text-start" style="max-width:140px;">
                                            @if($entry->observation)
                                                <span title="{{ $entry->observation }}">{{ \Illuminate\Support\Str::limit($entry->observation, 24) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-muted">{{ $entry->recordedBy?->fullName() ?? '—' }}</td>
                                        @if($canCapture)
                                            <td class="text-nowrap">
                                                @if($entry->isEditable())
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-entry"
                                                            data-id="{{ $entry->id }}"
                                                            data-recorded_at="{{ $entry->recorded_at->format('d/m/Y H:i') }}"
                                                            @foreach(array_merge(array_keys($inputCols), array_keys($outputCols)) as $f)
                                                                data-{{ $f }}="{{ $entry->$f }}"
                                                            @endforeach
                                                            data-observation="{{ $entry->observation }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-entry"
                                                            data-action="{{ route('fluidBalanceCaptures.destroy', $entry) }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>Totales</td>
                                    @foreach($inputCols as $field => $label)<td>{{ $entries->sum($field) ?: '' }}</td>@endforeach
                                    <td class="text-success">{{ $day->total_inputs_ml ?: '' }}</td>
                                    @foreach($outputCols as $field => $label)<td>{{ $entries->sum($field) ?: '' }}</td>@endforeach
                                    <td class="text-danger">{{ $day->total_measured_outputs_ml ?: '' }}</td>
                                    <td class="text-secondary">{{ $day->total_insensible_losses_ml ?: '' }}</td>
                                    <td class="{{ $day->net_balance_ml >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ $day->net_balance_ml >= 0 ? '+' : '' }}{{ $day->net_balance_ml }}
                                    </td>
                                    <td></td><td></td>@if($canCapture)<td></td>@endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

</div>{{-- /container --}}

@if($canCapture)
{{-- ════════ MODAL: nueva toma ════════ --}}
<div class="modal fade" id="newEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow">
            <form method="POST" action="{{ route('fluidBalanceCaptures.store', $order) }}">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-droplet me-1"></i>Registrar nueva toma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recorded_at" class="form-label fw-semibold">Hora de la toma</label>
                        <input type="datetime-local" id="recorded_at" name="recorded_at"
                               class="form-control" value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}"
                               max="{{ now()->format('Y-m-d\TH:i') }}" required>
                        <div class="form-text">El día del balance se determina automáticamente según esta hora.</div>
                    </div>
                    @include('fluid-balance-captures.partials.entry-fields')
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════ MODAL: editar toma ════════ --}}
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow">
            <form method="POST" id="editEntryForm">
                @csrf
                @method('PUT')
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i>Editar toma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Hora de la toma</label>
                        <div><i class="bi bi-clock me-1 text-muted"></i><span id="edit_recorded_at_label">—</span></div>
                        <div class="form-text">La hora no se puede cambiar. Las pérdidas insensibles no se recalculan al editar.</div>
                    </div>
                    @include('fluid-balance-captures.partials.entry-fields', ['prefix' => 'edit_'])
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
<form method="POST" id="deleteEntryForm" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($canCapture)
    const openModal = (id) => {
        const el = document.getElementById(id);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    };

    // Reabrir modal de captura si hubo errores de validación.
    @if($errors->any() && ! session('error'))
        openModal('newEntryModal');
    @endif

    // Abrir modal automáticamente si se llegó desde el acceso rápido de Hojas de Enfermería.
    @if($user->isAdmin() || $user->isNurse())
    if (window.location.hash === '#new-entry') {
        openModal('newEntryModal');
    }
    @endif

    const numericFields = @json(array_merge(array_keys($inputCols), array_keys($outputCols)));
    const editForm = document.getElementById('editEntryForm');

    document.querySelectorAll('.btn-edit-entry').forEach(function (btn) {
        btn.addEventListener('click', function () {
            editForm.action = '{{ url('fluid-balance-entries') }}/' + btn.dataset.id;
            document.getElementById('edit_recorded_at_label').textContent = btn.dataset.recorded_at || '—';
            numericFields.forEach(function (f) {
                const input = document.getElementById('edit_' + f);
                if (input) input.value = (btn.dataset[f] && btn.dataset[f] !== '0') ? btn.dataset[f] : '';
            });
            const obs = document.getElementById('edit_observation');
            if (obs) obs.value = btn.dataset.observation || '';
            openModal('editEntryModal');
        });
    });

    const delForm = document.getElementById('deleteEntryForm');
    document.querySelectorAll('.btn-delete-entry').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (confirm('¿Eliminar esta toma de balance?')) {
                delForm.action = btn.dataset.action;
                delForm.submit();
            }
        });
    });
    @endif
});
</script>
@endpush
