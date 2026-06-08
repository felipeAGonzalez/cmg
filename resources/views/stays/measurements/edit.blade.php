@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:560px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-rulers me-2"></i>Talla y peso — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('stays.measurements.update', $stay) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm p-4 mb-4">
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Datos del paciente <strong>{{ $stay->patient->fullName() }}</strong> para esta estancia.
                Ambos campos son opcionales.
            </p>

            <div class="row g-3">
                <div class="col-6">
                    <label for="height_cm" class="form-label fw-semibold">Talla</label>
                    <div class="input-group">
                        <input type="number" step="0.1" min="20" max="250"
                               id="height_cm" name="height_cm"
                               class="form-control @error('height_cm') is-invalid @enderror"
                               value="{{ old('height_cm', $stay->height_cm) }}">
                        <span class="input-group-text">cm</span>
                        @error('height_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-6">
                    <label for="weight_kg" class="form-label fw-semibold">Peso</label>
                    <div class="input-group">
                        <input type="number" step="0.1" min="0.5" max="500"
                               id="weight_kg" name="weight_kg"
                               class="form-control @error('weight_kg') is-invalid @enderror"
                               value="{{ old('weight_kg', $stay->weight_kg) }}">
                        <span class="input-group-text">kg</span>
                        @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Guardar
            </button>
            <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
