@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('stays.show', $stay) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0"><i class="bi bi-droplet-half me-2" style="color:#E91E63;"></i>Notas Transfusionales</h2>
            <p class="text-muted mb-0 small">
                Paciente: <strong>{{ $stay->patient->fullName() }}</strong>
                &middot; Cuarto {{ $stay->room->number ?? '—' }}
            </p>
        </div>
        @if($canCreate)
            <a href="{{ route('transfusionNotes.create', $stay) }}" class="btn btn-primary">
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
            <i class="bi bi-droplet" style="font-size:3rem;"></i>
            <p class="mt-3">No hay notas transfusionales registradas.</p>
            @if($canCreate)
                <a href="{{ route('transfusionNotes.create', $stay) }}" class="btn btn-primary">
                    Crear primera nota transfusional
                </a>
            @endif
        </div>
    @else
        <div class="list-group shadow-sm">
            @foreach($notes as $note)
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fw-semibold">
                            @if($note->start_datetime)
                                {{ $note->start_datetime->format('d/m/Y H:i') }}
                                @if($note->end_datetime)
                                    — {{ $note->end_datetime->format('H:i') }}
                                @endif
                            @else
                                Nota #{{ $note->id }}
                            @endif
                        </div>
                        <small class="text-muted">
                            Dr(a). {{ $note->attendingDoctor?->fullName() ?? '—' }}
                            &middot; {{ $note->filledSectionsCount() }} de 4 secciones con contenido
                            @if($note->transfusionChecklist)
                                &middot; Folio: {{ $note->transfusionChecklist->folio ?? $note->transfusionChecklist->id }}
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('transfusionNotes.show', $note) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        @if($canCreate)
                            <a href="{{ route('transfusionNotes.edit', $note) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        @endif
                        <a href="{{ route('transfusionNotes.pdf', $note) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        @if($canCreate)
                            <form method="POST" action="{{ route('transfusionNotes.destroy', $note) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar esta nota transfusional? Esta acción no se puede deshacer.');">
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
