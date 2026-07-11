@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('postSurgicalNoteTemplates.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <div>
                <h2 class="mb-0">{{ $template->name }}</h2>
                @if($template->description)
                    <p class="text-muted mb-0 small">{{ $template->description }}</p>
                @endif
            </div>
        </div>
        @if($template->owner_id === auth()->id())
            <div class="d-flex gap-2">
                <a href="{{ route('postSurgicalNoteTemplates.edit', $template) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <form method="POST" action="{{ route('postSurgicalNoteTemplates.duplicate', $template) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-files"></i> Duplicar
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <p class="text-muted small">
        <i class="bi bi-person me-1"></i>Propietario: {{ $template->owner->fullName() ?? '—' }}
        &middot; Última actualización: {{ $template->updated_at->format('d/m/Y H:i') }}
        &middot; {{ $template->filledSectionsCount() }} de 9 secciones con contenido
    </p>

    @foreach($sections as $key => $config)
        @php $content = $template->{$key}; @endphp
        @if(!empty(trim($content ?? '')))
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
                    {{ $config['label'] }}
                </div>
                <div class="card-body" style="white-space:pre-wrap;">{{ $content }}</div>
            </div>
        @endif
    @endforeach

    @if($template->filledSectionsCount() === 0)
        <div class="text-center text-muted py-4">
            <em>Esta plantilla no tiene contenido en ninguna sección.</em>
        </div>
    @endif
</div>
@endsection
