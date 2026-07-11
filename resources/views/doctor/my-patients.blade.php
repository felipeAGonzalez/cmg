@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard2-pulse me-2"></i>
            @if(auth()->user()->isDoctor())
                Mis Pacientes
            @else
                Pacientes
            @endif
        </h4>
        <p class="text-muted mb-0">
            @if(auth()->user()->isDoctor())
                Pacientes actualmente bajo tu atención y dados de alta previos.
            @else
                Todos los pacientes registrados en el sistema.
            @endif
        </p>
    </div>

    {{-- Tabs Activos / Dados de alta --}}
    <ul class="nav nav-tabs mb-0" style="border-bottom:none;">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}"
               href="{{ route('doctor.myPatients', ['tab' => 'active', 'search' => $search]) }}">
                <i class="bi bi-person-check me-1"></i>Activos
                @if($activeCount > 0)
                    <span class="badge bg-primary ms-1">{{ $activeCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'discharged' ? 'active' : '' }}"
               href="{{ route('doctor.myPatients', ['tab' => 'discharged', 'search' => $search]) }}">
                <i class="bi bi-archive me-1"></i>Dados de alta
                @if($dischargedCount > 0)
                    <span class="badge bg-secondary ms-1">{{ $dischargedCount }}</span>
                @endif
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm mb-3" style="border-top-left-radius:0; border-top-right-radius:0;">
        <div class="card-body pb-2 pt-3">

            {{-- Buscador --}}
            <form method="GET" action="{{ route('doctor.myPatients') }}" class="d-flex gap-2 flex-wrap mb-0" id="searchForm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if($tab === 'discharged')
                    <input type="hidden" name="pending_note" id="pendingNoteHidden" value="{{ $pendingNote ? '1' : '0' }}">
                @endif
                <div class="input-group" style="max-width:420px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search"
                           value="{{ $search }}"
                           placeholder="Buscar por nombre o apellido…"
                           class="form-control border-start-0 ps-0"
                           autocomplete="off">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    @if($search !== '')
                        <a href="{{ route('doctor.myPatients', ['tab' => $tab, 'pending_note' => $pendingNote ? '1' : '0']) }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>

            {{-- Filtro "Solo pendientes de Nota de Alta" (solo en tab discharged) --}}
            @if($tab === 'discharged')
                <div class="mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="pendingNoteFilter"
                               {{ $pendingNote ? 'checked' : '' }}
                               onchange="togglePendingFilter(this.checked)">
                        <label class="form-check-label" for="pendingNoteFilter">
                            <i class="bi bi-file-earmark-x me-1"></i>
                            Solo pendientes de Nota de Alta
                            @if($pendingCount > 0)
                                <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
                            @endif
                        </label>
                    </div>
                    @if($pendingNote && auth()->user()->isDoctor())
                        <small class="text-muted ms-4">
                            <i class="bi bi-info-circle me-1"></i>Filtro activado por defecto. Desactívalo para ver todos tus pacientes dados de alta.
                        </small>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tabla de resultados --}}
        @if($stays->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-person-x text-muted" style="font-size:3rem;"></i>
                <p class="mt-3 text-muted mb-0">
                    @if($search !== '' && $tab === 'discharged' && $pendingNote)
                        No se encontraron pacientes con nota pendiente y nombre <strong>"{{ $search }}"</strong>.
                    @elseif($search !== '')
                        No se encontraron pacientes con <strong>"{{ $search }}"</strong>
                        en la pestaña "{{ $tab === 'active' ? 'Activos' : 'Dados de alta' }}".
                    @elseif($tab === 'discharged' && $pendingNote)
                        @if(auth()->user()->isDoctor())
                            No tienes pacientes dados de alta con Nota de Alta pendiente.
                        @else
                            No hay pacientes dados de alta con Nota de Alta pendiente.
                        @endif
                    @elseif($tab === 'active')
                        @if(auth()->user()->isDoctor())
                            Actualmente no tienes pacientes activos asignados.
                        @else
                            No hay pacientes activos en este momento.
                        @endif
                    @else
                        @if(auth()->user()->isDoctor())
                            No tienes pacientes dados de alta en el historial.
                        @else
                            No hay pacientes dados de alta en el historial.
                        @endif
                    @endif
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            @if($tab === 'active')
                                <th>Cuarto</th>
                            @endif
                            <th>Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Ingreso</th>
                            @if($tab === 'discharged')
                                <th>Alta</th>
                                <th>Nota de Alta</th>
                            @endif
                            <th>Médico(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stays as $stay)
                        @php
                            $note = $stay->dischargeNote;
                            if ($tab === 'discharged') {
                                if (!$note) {
                                    $noteLabel = 'Sin nota';
                                    $noteBadge = 'bg-danger';
                                } elseif (!$note->isComplete()) {
                                    $noteLabel = 'Incompleta';
                                    $noteBadge = 'bg-warning text-dark';
                                } else {
                                    $noteLabel = 'Completa';
                                    $noteBadge = 'bg-success';
                                }
                            }
                        @endphp
                        <tr style="cursor:pointer;"
                            onclick="window.location='{{ route('doctor.patientDetail', $stay) }}'"
                            @if($tab === 'active' && $stay->hasDischargeIndicated()) class="table-warning" @endif>

                            @if($tab === 'active')
                                <td class="fw-semibold">Cuarto {{ $stay->room->number ?? '—' }}</td>
                            @endif

                            <td>
                                {{ $stay->patient->fullName() }}
                                @if($tab === 'active' && $stay->hasDischargeIndicated())
                                    <span class="badge bg-warning text-dark ms-1">
                                        <i class="bi bi-clock-history"></i> Alta indicada
                                    </span>
                                @elseif($tab === 'discharged')
                                    <span class="badge bg-secondary ms-1">Alta</span>
                                @endif
                            </td>

                            <td class="text-muted">{{ Str::limit($stay->diagnosis, 60) }}</td>

                            <td class="text-nowrap">
                                {{ $stay->admission_date->format('d/m/Y') }}
                            </td>

                            @if($tab === 'discharged')
                                <td class="text-nowrap">
                                    {{ $stay->discharge_date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $noteBadge }}">
                                        <i class="bi bi-file-earmark-text me-1"></i>{{ $noteLabel }}
                                    </span>
                                </td>
                            @endif

                            <td class="small text-muted">
                                @if($stay->currentDoctors->isNotEmpty())
                                    {{ $stay->currentDoctors->map(fn($sd) => 'Dr(a). ' . ($sd->doctor?->name ?? '—'))->implode(', ') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($tab === 'discharged')
<script>
function togglePendingFilter(checked) {
    document.getElementById('pendingNoteHidden').value = checked ? '1' : '0';
    document.getElementById('searchForm').submit();
}
</script>
@endif

@endsection
