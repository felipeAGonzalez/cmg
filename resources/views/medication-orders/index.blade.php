@extends('layouts.app')

@php
    $user = auth()->user();
    $canPrescribe = $stay->isActive() && (
        $user->isAdmin()
        || ($user->isDoctor() && $stay->currentDoctors->where('doctor_id', $user->id)->count() > 0)
        || ($user->isNurse() && $stay->currentDoctors->count() > 0)
    );
@endphp

@section('content')
<div class="container py-4">

    {{-- ════════ ENCABEZADO ════════ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-prescription2 text-primary me-2"></i>Indicaciones del paciente</h4>
                <div>
                    <span class="fw-semibold">{{ $stay->patient->fullName() }}</span>
                    <span class="text-muted">· {{ $stay->patient->age() }} años · Cuarto {{ $stay->room->number }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('stays.show', ['room' => $stay->room, 'stay' => $stay->id]) }}#indications"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al paciente
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>@endif

    @php $otherCount = ($activeFluidBalanceOrder ? 1 : 0) + $pastFluidBalanceOrders->count(); @endphp

    {{-- ════════ SUB-TABS INTERNAS ════════ --}}
    <ul class="nav nav-pills mb-4" id="indicationsTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#medications-tab" type="button">
                <i class="bi bi-capsule me-1"></i>Medicamentos
                <span class="badge bg-light text-dark ms-1">{{ $orders->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#other-indications-tab" type="button">
                <i class="bi bi-clipboard2-pulse me-1"></i>Otras indicaciones
                <span class="badge bg-light text-dark ms-1">{{ $otherCount }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
    {{-- ════════════════════════════════════════════ --}}
    {{-- TAB MEDICAMENTOS                              --}}
    {{-- ════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="medications-tab" role="tabpanel">

    @if($canPrescribe)
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('medicationOrders.create', $stay) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Nueva prescripción
            </a>
        </div>
    @endif

    @if($orders->isEmpty())
        {{-- ════════ VISTA VACÍA ════════ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-capsule text-muted" style="font-size:2.5rem;"></i>
                <p class="text-muted mt-2 mb-3">Este paciente aún no tiene prescripciones registradas.</p>
                @if($canPrescribe)
                    <a href="{{ route('medicationOrders.create', $stay) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Crear primera prescripción
                    </a>
                @endif
            </div>
        </div>
    @else

        {{-- ════════ ACTIVAS ════════ --}}
        <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle me-1"></i>Prescripciones activas ({{ $activeOrders->count() }})</h6>
        @if($activeOrders->isEmpty())
            <p class="text-muted fst-italic">Sin prescripciones activas.</p>
        @else
            <div class="row g-3 mb-4">
                @foreach($activeOrders as $order)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-1">{{ $order->medication_name }} <span class="text-muted fw-normal">· {{ $order->dose }}</span></h6>
                                <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <div class="small mb-1"><i class="bi bi-signpost me-1"></i>{{ $order->routeLabel() }} · {{ $order->frequencyLabel() }}</div>
                            <div class="small text-muted mb-1">
                                <i class="bi bi-calendar-event me-1"></i>Inicio: {{ $order->start_date->format('d/m/Y') }}
                                @if($order->progressLabel())
                                    · {{ $order->progressLabel() }}
                                    @if($order->daysRemaining() !== null)
                                        ({{ $order->daysRemaining() }} día(s) restante(s))
                                    @endif
                                @else
                                    · Sin duración definida
                                @endif
                            </div>
                            <div class="small text-muted mb-1">
                                Prescrita por Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}.
                                Capturada por {{ $order->createdBy?->fullName() ?? '—' }}
                                el {{ $order->created_at->format('d/m/Y H:i') }}.
                            </div>
                            @if($order->updated_by_id)
                                <div class="small text-muted mb-1">
                                    Última edición: {{ $order->updatedBy?->fullName() ?? '—' }} el {{ $order->updated_at->format('d/m/Y H:i') }}.
                                </div>
                            @endif
                            @if($order->indications)
                                <div class="mt-2 p-2 bg-light rounded small" style="white-space:pre-wrap;">{{ $order->indications }}</div>
                            @endif

                            {{-- Administraciones recientes --}}
                            @php $recent = $order->recentAdministrations(5); @endphp
                            <div class="mt-3 pt-2 border-top">
                                <small class="text-muted d-block mb-1">
                                    <strong>Últimas administraciones</strong> ({{ $order->administrationsCount() }} en total)
                                </small>
                                @if($recent->isEmpty())
                                    <small class="text-muted">Sin administraciones registradas aún.</small>
                                @else
                                    <ul class="list-unstyled small mb-2">
                                        @foreach($recent as $a)
                                        <li>
                                            <span class="badge {{ $a->statusBadgeClass() }}" style="font-size:.7em;">{{ $a->statusLabel() }}</span>
                                            {{ $a->administered_at->format('d/m H:i') }} —
                                            {{ $a->actual_dose }} —
                                            {{ $a->recordedBy?->fullName() ?? '—' }}
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if($stay->isActive() && ($user->isAdmin() || $user->isNurse()))
                                    <a href="{{ route('medicationAdministrations.create', ['stay' => $stay, 'medication_order_id' => $order->id]) }}"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-circle"></i> Registrar administración
                                    </a>
                                @endif
                            </div>

                            @if($order->canBeModifiedBy($user))
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('medicationOrders.edit', $order) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="{{ route('medicationOrders.suspendForm', $order) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pause-circle"></i> Suspender
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- ════════ ACORDEÓN: SUSPENDIDAS / FINALIZADAS ════════ --}}
        <div class="accordion" id="ordersAccordion">

            {{-- Suspendidas --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#suspendedColl">
                        <i class="bi bi-pause-circle me-2 text-warning"></i>Prescripciones suspendidas ({{ $suspendedOrders->count() }})
                    </button>
                </h2>
                <div id="suspendedColl" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                    <div class="accordion-body">
                        @if($suspendedOrders->isEmpty())
                            <p class="text-muted fst-italic mb-0">Sin prescripciones suspendidas.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th>Medicamento</th><th>Dosis</th><th>Suspendida por</th><th>Fecha</th><th>Motivo</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suspendedOrders as $order)
                                        <tr>
                                            <td>{{ $order->medication_name }}</td>
                                            <td>{{ $order->dose }}</td>
                                            <td class="text-muted small">{{ $order->suspendedBy?->fullName() ?? '—' }}</td>
                                            <td class="text-nowrap small">{{ $order->suspended_at?->format('d/m/Y H:i') }}</td>
                                            <td class="small">{{ $order->suspension_reason }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Finalizadas --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#finishedColl">
                        <i class="bi bi-check2-circle me-2 text-secondary"></i>Prescripciones finalizadas ({{ $finishedOrders->count() }})
                    </button>
                </h2>
                <div id="finishedColl" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                    <div class="accordion-body">
                        @if($finishedOrders->isEmpty())
                            <p class="text-muted fst-italic mb-0">Sin prescripciones finalizadas.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th>Medicamento</th><th>Dosis</th><th>Inicio</th><th>Duración</th><th>Finalizada el</th><th>Prescrita por</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($finishedOrders as $order)
                                        <tr>
                                            <td>{{ $order->medication_name }}</td>
                                            <td>{{ $order->dose }}</td>
                                            <td class="text-nowrap small">{{ $order->start_date->format('d/m/Y') }}</td>
                                            <td class="small">{{ $order->duration_days }} día(s)</td>
                                            <td class="text-nowrap small">{{ $order->endDate()?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-muted small">Dr(a). {{ $order->prescribedBy?->fullName() ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    @endif

    </div>{{-- /tab medications --}}

    {{-- ════════════════════════════════════════════ --}}
    {{-- TAB OTRAS INDICACIONES                        --}}
    {{-- ════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="other-indications-tab" role="tabpanel">

        {{-- ──── Balance de líquidos ──── --}}
        @php
            $canStartBalance = $stay->isActive() && (
                $user->isAdmin()
                || ($user->isDoctor() && $stay->currentDoctors->where('doctor_id', $user->id)->count() > 0)
                || ($user->isNurse() && $stay->currentDoctors->count() > 0)
            );
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-droplet-half me-1"></i>Balance de líquidos</h6>
                @if(! $activeFluidBalanceOrder && $canStartBalance)
                    <a href="{{ route('fluidBalanceOrders.create', $stay) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Iniciar balance
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($activeFluidBalanceOrder)
                    {{-- Orden activa --}}
                    <div class="alert alert-success border-0 d-flex justify-content-between align-items-start gap-2 mb-0">
                        <div>
                            <strong>
                                <span class="badge bg-success">Activa</span>
                                Balance de líquidos en curso
                            </strong>
                            <div class="mt-1 small">
                                Iniciado el {{ $activeFluidBalanceOrder->start_date->format('d/m/Y') }}
                                por Dr(a). {{ $activeFluidBalanceOrder->prescribedBy?->fullName() ?? '—' }}.
                            </div>
                            @if($activeFluidBalanceOrder->clinical_reason)
                                <div class="mt-2 small"><strong>Motivo clínico:</strong> {{ $activeFluidBalanceOrder->clinical_reason }}</div>
                            @endif
                            <div class="mt-1 small text-muted">
                                Capturada por {{ $activeFluidBalanceOrder->createdBy?->fullName() ?? '—' }}
                                el {{ $activeFluidBalanceOrder->created_at->format('d/m/Y H:i') }}.
                            </div>
                        </div>
                        @if($activeFluidBalanceOrder->canBeModifiedBy($user))
                            <a href="{{ route('fluidBalanceOrders.suspendForm', $activeFluidBalanceOrder) }}"
                               class="btn btn-sm btn-outline-warning text-nowrap">
                                <i class="bi bi-pause-circle me-1"></i>Suspender
                            </a>
                        @endif
                    </div>

                    {{-- Captura hora por hora del balance (Fase 8.B) --}}
                    <div class="mt-3">
                        <a href="{{ route('fluidBalanceCaptures.index', $activeFluidBalanceOrder) }}" class="btn btn-primary">
                            <i class="bi bi-droplet me-1"></i>Registrar balance
                        </a>
                    </div>
                @else
                    <p class="text-muted mb-0">
                        El paciente no tiene actualmente una orden de balance de líquidos activa.
                    </p>
                @endif

                {{-- Histórico de órdenes anteriores --}}
                @if($pastFluidBalanceOrders->isNotEmpty())
                    <hr>
                    <div class="accordion accordion-flush" id="pastBalanceOrders">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#pastBalanceOrdersCollapse">
                                    <i class="bi bi-clock-history me-2"></i>Historial de órdenes anteriores ({{ $pastFluidBalanceOrders->count() }})
                                </button>
                            </h2>
                            <div id="pastBalanceOrdersCollapse" class="accordion-collapse collapse" data-bs-parent="#pastBalanceOrders">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Inicio</th>
                                                    <th>Cierre</th>
                                                    <th>Motivo del cierre</th>
                                                    <th>Prescrita por</th>
                                                    <th>Estado</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pastFluidBalanceOrders as $o)
                                                    <tr>
                                                        <td class="text-nowrap small">{{ $o->start_date->format('d/m/Y') }}</td>
                                                        <td class="text-nowrap small">{{ $o->suspended_at?->format('d/m/Y H:i') }}</td>
                                                        <td class="small">{{ $o->suspension_reason }}</td>
                                                        <td class="text-muted small">Dr(a). {{ $o->prescribedBy?->fullName() ?? '—' }}</td>
                                                        <td><span class="badge {{ $o->statusBadgeClass() }}">{{ $o->statusLabel() }}</span></td>
                                                        <td class="text-end">
                                                            @if($o->days()->exists())
                                                                <a href="{{ route('fluidBalanceCaptures.index', $o) }}" class="btn btn-sm btn-outline-primary text-nowrap">
                                                                    <i class="bi bi-droplet me-1"></i>Ver registros
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Espacio reservado para futuras "otras indicaciones" --}}
        <p class="text-muted text-center mt-4">
            <i class="bi bi-info-circle me-1"></i>
            Próximamente se agregarán más tipos de indicaciones médicas en esta sección.
        </p>

    </div>{{-- /tab other-indications --}}

    </div>{{-- /tab-content --}}

</div>{{-- /container --}}
@endsection
