@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2><i class="bi bi-lungs"></i> Notas de Anestesia</h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $stay->patient->fullName() }}</strong> &nbsp;·&nbsp;
                Estancia #{{ $stay->id }}
                @if($stay->room) — Hab. {{ $stay->room->number }} @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stays.show', $stay) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a estancia
            </a>
            @if($canCreate)
                <a href="{{ route('anesthesiaNotes.create', $stay) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva nota
                </a>
            @endif
        </div>
    </div>

    @if($notes->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-lungs" style="font-size:3rem;"></i>
            <p class="mt-3">No hay Notas de Anestesia registradas para esta estancia.</p>
            @if($canCreate)
                <a href="{{ route('anesthesiaNotes.create', $stay) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear primera nota
                </a>
            @endif
        </div>
    @else
        <div class="row g-3">
            @foreach($notes as $note)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0">
                                        Nota #{{ $note->id }}
                                        @if($note->surgery_urgency)
                                            <span class="badge {{ $note->surgery_urgency === 'urgencia' ? 'bg-danger' : 'bg-info text-dark' }} ms-1">
                                                {{ ucfirst($note->surgery_urgency) }}
                                            </span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $note->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>

                            @if($note->preop_diagnosis)
                                <p class="small text-muted mb-1">
                                    <strong>Dx pre:</strong>
                                    {{ \Illuminate\Support\Str::limit($note->preop_diagnosis, 60) }}
                                </p>
                            @endif

                            @if($note->postSurgicalNote)
                                <p class="small text-muted mb-1">
                                    <i class="bi bi-link-45deg"></i>
                                    Vinculada a nota postqx del {{ $note->postSurgicalNote->surgery_date?->format('d/m/Y') ?? '—' }}
                                </p>
                            @endif

                            <p class="text-muted small mb-2">
                                <i class="bi bi-person-badge"></i>
                                {{ $note->attendingDoctor?->name ?? '—' }}
                            </p>

                            @php
                                $hasSection1 = !empty(trim($note->preop_diagnosis ?? '')) || !empty(trim($note->current_illness ?? ''));
                                $hasSection2 = !empty(trim($note->postop_diagnosis ?? '')) || !empty(trim($note->performed_surgery ?? ''));
                                $hasSection3 = !empty(trim($note->anesthetic_technique_and_drugs ?? '')) || !empty(trim($note->evolution_and_ucpa_discharge ?? ''));
                            @endphp
                            <div class="mb-2">
                                <span class="badge {{ $hasSection1 ? 'bg-success' : 'bg-secondary' }} me-1">Valoración</span>
                                <span class="badge {{ $hasSection2 ? 'bg-success' : 'bg-secondary' }} me-1">Registro</span>
                                <span class="badge {{ $hasSection3 ? 'bg-success' : 'bg-secondary' }}">Post Anestésica</span>
                            </div>

                            <div class="mt-auto d-flex gap-1 flex-wrap">
                                <a href="{{ route('anesthesiaNotes.show', $note) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                @if($canCreate)
                                    <a href="{{ route('anesthesiaNotes.edit', $note) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                @endif
                                <a href="{{ route('anesthesiaNotes.pdf', $note) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </a>
                                @if($canCreate)
                                    <form method="POST" action="{{ route('anesthesiaNotes.destroy', $note) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta nota? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
