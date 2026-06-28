@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('evolutionTemplates.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h2 class="mb-0">Nueva plantilla de Evolución</h2>
    </div>

    <form method="POST" action="{{ route('evolutionTemplates.store') }}">
        @csrf
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Datos de la plantilla</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" maxlength="100" required
                               value="{{ old('name') }}"
                               placeholder="Ej. Evolución postquirúrgica, Seguimiento nefrológico..."
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción (opcional)</label>
                        <input type="text" name="description" maxlength="500"
                               value="{{ old('description') }}"
                               placeholder="Breve descripción de cuándo usar esta plantilla."
                               class="form-control @error('description') is-invalid @enderror">
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Contenido de la plantilla (SOAP)</h6>
                <small class="text-muted">Todas las secciones son opcionales. Llena solo las que quieras como base para futuras notas de evolución.</small>
            </div>
            <div class="card-body">
                @foreach($sections as $key => $section)
                    <div class="mb-4">
                        <label class="form-label"><strong>{{ $loop->iteration }}. {{ $section['label'] }}</strong></label>
                        <textarea name="{{ $key }}" rows="4"
                                  placeholder="{{ $section['placeholder'] }}"
                                  class="form-control @error($key) is-invalid @enderror">{{ old($key) }}</textarea>
                        @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('evolutionTemplates.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar plantilla
            </button>
        </div>
    </form>
</div>
@endsection
