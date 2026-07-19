@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:800px;">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('anesthesiaNoteTemplates.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0"><i class="bi bi-lungs"></i> {{ $template->name }}</h2>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            @if($isAdmin && $template->owner_id !== auth()->id())
                <p class="text-muted small"><i class="bi bi-person"></i> Propietario: {{ $template->owner->name }}</p>
            @endif
            @if($template->description)
                <p class="text-muted">{{ $template->description }}</p>
            @endif
            <p class="text-muted small mb-0">
                <i class="bi bi-list-check"></i>
                {{ $template->filledSectionsCount() }} de 5 secciones con contenido &nbsp;·&nbsp;
                Actualizado {{ $template->updated_at->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>

    @foreach($sections as $key => $label)
        @if(!empty(trim($template->{$key} ?? '')))
            <div class="card mb-3">
                <div class="card-header" style="background-color:#fce4ec; color:#880e4f;">
                    <strong>{{ $label }}</strong>
                </div>
                <div class="card-body">
                    <p style="white-space:pre-wrap; margin:0;">{{ $template->{$key} }}</p>
                </div>
            </div>
        @endif
    @endforeach

    <div class="d-flex gap-2 flex-wrap">
        @if($template->owner_id === auth()->id())
            <a href="{{ route('anesthesiaNoteTemplates.edit', $template) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <form method="POST" action="{{ route('anesthesiaNoteTemplates.duplicate', $template) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-files"></i> Duplicar
                </button>
            </form>
        @endif
        @if($template->owner_id === auth()->id() || auth()->user()->isAdmin())
            <form method="POST" action="{{ route('anesthesiaNoteTemplates.destroy', $template) }}" class="d-inline"
                  onsubmit="return confirm('¿Eliminar esta plantilla?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
