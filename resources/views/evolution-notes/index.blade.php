@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('stays.show', $stay) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0"><i class="bi bi-arrow-up-right-circle me-2" style="color:#E91E63;"></i>Notas de Evolución</h2>
            <p class="text-muted mb-0 small">
                Paciente: <strong>{{ $stay->patient->fullName() }}</strong>
                &middot; Cuarto {{ $stay->room->number ?? '—' }}
            </p>
        </div>
        @if($canCreate)
            <a href="{{ route('evolutionNotes.create', $stay) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva nota
            </a>
        @endif
    </div>

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

    @if($notes->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-text" style="font-size:3rem;"></i>
            <p class="mt-3">No hay notas de evolución registradas.</p>
            @if($canCreate)
                <a href="{{ route('evolutionNotes.create', $stay) }}" class="btn btn-primary">
                    Crear primera nota de evolución
                </a>
            @endif
        </div>
    @else
        <div class="list-group shadow-sm">
            @foreach($notes as $note)
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fw-semibold">
                            {{ $note->note_datetime->format('d/m/Y H:i') }}
                        </div>
                        <small class="text-muted">
                            Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
                            &middot; {{ $note->filledSectionsCount() }} de 7 secciones con contenido
                        </small>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('evolutionNotes.show', $note) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        @if($canCreate)
                            <a href="{{ route('evolutionNotes.edit', $note) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        @endif
                        <a href="{{ route('evolutionNotes.pdf', $note) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        @if($canCreate)
                            <form method="POST" action="{{ route('evolutionNotes.destroy', $note) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar esta nota de evolución? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
