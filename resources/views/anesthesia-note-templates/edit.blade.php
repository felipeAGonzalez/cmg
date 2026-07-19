@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:800px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('anesthesiaNoteTemplates.show', $template) }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0"><i class="bi bi-lungs"></i> Editar plantilla: {{ $template->name }}</h2>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('anesthesiaNoteTemplates.update', $template) }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre de la plantilla <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required maxlength="100">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Descripción (opcional)</label>
                    <textarea name="description" class="form-control" rows="2" maxlength="500">{{ old('description', $template->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header" style="background-color:#E91E63; color:white;">
                <strong>Secciones narrativas</strong>
            </div>
            <div class="card-body">
                @foreach($sections as $key => $label)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ $label }}</label>
                        <textarea name="{{ $key }}" class="form-control" rows="4">{{ old($key, $template->{$key}) }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Guardar cambios</button>
            <a href="{{ route('anesthesiaNoteTemplates.show', $template) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
