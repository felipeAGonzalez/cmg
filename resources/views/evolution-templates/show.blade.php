@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $template->name }}</h2>
            @if($template->description)
                <p class="text-muted mb-0">{{ $template->description }}</p>
            @endif
            <p class="text-muted small mb-0">
                Dueño: {{ $template->owner->name }}
                &middot; Actualizado: {{ $template->updated_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($template->owner_id === auth()->id())
                <a href="{{ route('evolutionTemplates.edit', $template) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <form method="POST" action="{{ route('evolutionTemplates.duplicate', $template) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-files"></i> Duplicar
                    </button>
                </form>
            @endif
            <a href="{{ route('evolutionTemplates.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @foreach($sections as $key => $section)
        @php $content = $template->{$key}; @endphp
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">{{ $loop->iteration }}. {{ $section['label'] }}</h6>
            </div>
            <div class="card-body">
                @if(!empty(trim($content ?? '')))
                    <div style="white-space:pre-wrap;">{{ $content }}</div>
                @else
                    <p class="text-muted small mb-0"><em>Sin contenido en esta sección.</em></p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
