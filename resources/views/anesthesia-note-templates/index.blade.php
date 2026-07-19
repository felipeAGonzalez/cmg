@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-lungs"></i>
                @if($isAdmin) Plantillas de Nota de Anestesia (todas) @else Mis plantillas de Nota de Anestesia @endif
            </h2>
            <p class="text-muted mb-0">
                @if($isAdmin)
                    Vista de auditoría: puedes ver todas las plantillas pero solo eliminar.
                @else
                    Plantillas reutilizables para las Notas de Anestesia.
                @endif
            </p>
        </div>
        @if(!$isAdmin)
            <a href="{{ route('anesthesiaNoteTemplates.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva plantilla
            </a>
        @endif
    </div>

    @if($templates->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-lungs" style="font-size:3rem;"></i>
            @if($isAdmin)
                <p class="mt-3">No hay plantillas de Nota de Anestesia en el sistema.</p>
            @else
                <p class="mt-3">No tienes plantillas de Nota de Anestesia todavía.</p>
                <p><a href="{{ route('anesthesiaNoteTemplates.create') }}" class="btn btn-primary">Crear mi primera plantilla</a></p>
            @endif
        </div>
    @else
        <div class="row g-3">
            @foreach($templates as $template)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ $template->name }}</h5>
                            @if($isAdmin && $template->owner_id !== auth()->id())
                                <small class="text-muted mb-2"><i class="bi bi-person"></i> {{ $template->owner->name }}</small>
                            @endif
                            @if($template->description)
                                <p class="text-muted small mb-2">{{ $template->description }}</p>
                            @endif
                            <p class="text-muted small mb-3">
                                <i class="bi bi-list-check"></i>
                                {{ $template->filledSectionsCount() }} de 5 secciones con contenido
                            </p>
                            <div class="text-muted small mb-3">Última actualización: {{ $template->updated_at->format('d/m/Y H:i') }}</div>
                            <div class="mt-auto d-flex gap-1 flex-wrap">
                                <a href="{{ route('anesthesiaNoteTemplates.show', $template) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                @if($template->owner_id === auth()->id())
                                    <a href="{{ route('anesthesiaNoteTemplates.edit', $template) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <form method="POST" action="{{ route('anesthesiaNoteTemplates.duplicate', $template) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-files"></i> Duplicar
                                        </button>
                                    </form>
                                @endif
                                @if($template->owner_id === auth()->id() || auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('anesthesiaNoteTemplates.destroy', $template) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta plantilla? Esta acción no se puede deshacer.');">
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
