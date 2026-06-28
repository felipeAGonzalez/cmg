@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('evolutionNotes.index', $stay) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <div>
                <h2 class="mb-0">Nota de Evolución</h2>
                <p class="text-muted mb-0 small">{{ $note->note_datetime->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($stay->discharge_date === null)
                <a href="{{ route('evolutionNotes.edit', $note) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endif
            <a href="{{ route('evolutionNotes.pdf', $note) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Ver PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Datos del paciente --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-light fw-semibold">Datos del paciente</div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6"><strong>Nombre:</strong> {{ $patient->fullName() }}</div>
                <div class="col-md-3"><strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}</div>
                <div class="col-md-3"><strong>Expediente:</strong> {{ $patient->id }}</div>
                <div class="col-md-6"><strong>Médico:</strong> Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}</div>
                <div class="col-md-3">
                    <strong>F. Ingreso:</strong> {{ $stay->admission_date->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Signos vitales (más recientes) --}}
    <div class="card mb-3 border-0 shadow-sm" style="background:#f8f9fa;">
        <div class="card-body small">
            <h6 class="mb-2"><i class="bi bi-heart-pulse me-1" style="color:#E91E63;"></i>Signos vitales (últimos registrados)</h6>
            @if($latestVitals)
                <div class="row g-2">
                    <div class="col-auto"><strong>FC:</strong> {{ $latestVitals->heart_rate ?? '—' }}</div>
                    <div class="col-auto"><strong>TA:</strong>
                        {{ $latestVitals->blood_pressure_systolic ?? '—' }}/{{ $latestVitals->blood_pressure_diastolic ?? '—' }}
                    </div>
                    <div class="col-auto"><strong>Temp:</strong> {{ $latestVitals->temperature ?? '—' }}°C</div>
                    <div class="col-auto"><strong>FR:</strong> {{ $latestVitals->respiratory_rate ?? '—' }}</div>
                    <div class="col-auto text-muted">{{ $latestVitals->recorded_at->format('d/m/Y H:i') }}</div>
                </div>
            @else
                <p class="text-muted mb-0">Sin signos vitales registrados.</p>
            @endif
        </div>
    </div>

    {{-- Secciones SOAP --}}
    @foreach($sections as $key => $config)
        @php $content = $note->{$key}; @endphp
        @if(!empty(trim($content ?? '')))
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
                    {{ $config['label'] }}
                </div>
                <div class="card-body" style="white-space:pre-wrap;">{{ $content }}</div>
            </div>
        @endif
    @endforeach

    @if($note->filledSectionsCount() === 0)
        <div class="text-center text-muted py-4">
            <em>Esta nota no tiene contenido en ninguna sección.</em>
        </div>
    @endif

    {{-- Medicamentos en el rango --}}
    @if($medications->isNotEmpty())
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-capsule me-1"></i>Medicamentos administrados
                <small class="text-muted fw-normal">
                    ({{ $note->medications_from->format('d/m/Y H:i') }}
                    — {{ $note->medications_to->format('d/m/Y H:i') }})
                </small>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosis</th>
                            <th>Vía</th>
                            <th>Hora</th>
                            <th>Enfermera</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medications as $adm)
                            <tr>
                                <td>{{ $adm->medicationOrder->medication_name ?? '—' }}</td>
                                <td>{{ $adm->medicationOrder->dose ?? '—' }}</td>
                                <td>{{ $adm->medicationOrder->routeLabel() ?? '—' }}</td>
                                <td>{{ $adm->administered_at?->format('d/m H:i') ?? '—' }}</td>
                                <td>{{ $adm->recordedBy?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($note->medications_from && $note->medications_to)
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body text-muted small">
                <i class="bi bi-capsule me-1"></i>Sin medicamentos administrados en el rango
                ({{ $note->medications_from->format('d/m/Y H:i') }}
                — {{ $note->medications_to->format('d/m/Y H:i') }}).
            </div>
        </div>
    @endif

    {{-- Metadatos --}}
    <p class="text-muted small mt-3">
        Creada por {{ $note->createdBy?->fullName() ?? '—' }}
        el {{ $note->created_at->format('d/m/Y H:i') }}.
        @if($note->updatedBy)
            Última edición: {{ $note->updatedBy->fullName() }} el {{ $note->updated_at->format('d/m/Y H:i') }}.
        @endif
    </p>
</div>
@endsection
