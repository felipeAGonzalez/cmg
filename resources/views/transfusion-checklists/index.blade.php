@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('stays.show', ['room' => $stay->room_id]) }}"
           class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0">
                <i class="bi bi-droplet-half"></i> Transfusiones
            </h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $stay->patient->fullName() }}</strong>
                · Cuarto {{ $stay->room->number ?? '—' }}
            </p>
        </div>
        @if($canCreate)
            <a href="{{ route('transfusionChecklists.create', $stay) }}"
               class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva transfusión
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

    @if($checklists->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-droplet" style="font-size:3rem;"></i>
            <p class="mt-3">No hay transfusiones registradas para este paciente.</p>
            @if($canCreate)
                <a href="{{ route('transfusionChecklists.create', $stay) }}"
                   class="btn btn-primary">
                    Registrar primera transfusión
                </a>
            @endif
        </div>
    @else
        <div class="list-group">
            @foreach($checklists as $c)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <strong>Transfusión #{{ $c->id }}</strong>
                            <span class="badge {{ $c->statusBadgeClass() }}">
                                {{ $c->statusLabel() }}
                            </span>
                            @if($c->folio)
                                <small class="text-muted">Folio: {{ $c->folio }}</small>
                            @endif
                        </div>
                        <small class="text-muted">
                            Iniciada: {{ $c->started_at->format('d/m/Y H:i') }}
                            @if($c->finalized_at)
                                · Finalizada: {{ $c->finalized_at->format('d/m/Y H:i') }}
                            @endif
                            @if($c->product_group && $c->product_rh_factor)
                                · {{ $c->product_group }}{{ $c->product_rh_factor }}
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('transfusionChecklists.show', $c) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        @if($canCreate)
                            <a href="{{ route('transfusionChecklists.edit', $c) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        @endif
                        @if($c->isFinalized())
                            <a href="{{ route('transfusionChecklists.pdf', $c) }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        @endif
                        @if(!$c->isFinalized() && $canCreate)
                            <form method="POST"
                                  action="{{ route('transfusionChecklists.destroy', $c) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('¿Eliminar esta transfusión? Esta acción no se puede deshacer.');">
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
