@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-journal-text me-2"></i>Editar registro — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="small">
            <span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong>
            <span class="text-muted ms-2">· {{ $stay->patient->age() }} años</span>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('nursingEntries.update', $entry) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm p-4 mb-4">

            {{-- Categoría (solo lectura) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Categoría</label>
                <span class="badge {{ $entry->categoryBadgeClass() }} fs-6">
                    <i class="bi {{ $entry->categoryIcon() }} me-1"></i>{{ $entry->categoryLabel() }}
                </span>
                <div class="form-text">La categoría no se puede cambiar. Si fue un error, elimina el registro y crea uno nuevo.</div>
            </div>

            {{-- Hora (solo lectura) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Hora del registro</label>
                <div>
                    <i class="bi bi-clock me-1 text-muted"></i>{{ $entry->recorded_at->format('d/m/Y H:i') }}
                    <span class="text-muted small">· {{ $entry->shiftLabel() }}</span>
                </div>
            </div>

            {{-- Descripción (editable) --}}
            <div class="mb-1">
                <label for="description" class="form-label fw-semibold">Descripción</label>
                <textarea id="description" name="description" rows="4" minlength="3" maxlength="2000"
                          class="form-control @error('description') is-invalid @enderror"
                          required>{{ old('description', $entry->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Actualizar</button>
            <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
