@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-droplet-half"></i> Nueva transfusión</h2>
    <p class="text-muted">
        Paciente: <strong>{{ $stay->patient->fullName() }}</strong>
    </p>

    <form method="POST" action="{{ route('transfusionChecklists.store', $stay) }}">
        @csrf
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Folio (opcional)</label>
                    <input type="text" name="folio" maxlength="50"
                           value="{{ old('folio') }}"
                           class="form-control"
                           placeholder="Ej. T-2026-001">
                </div>
                <p class="text-muted small mb-0">
                    Al iniciar, se registrará la hora actual como inicio de la
                    transfusión. Continuarás capturando el checklist en la
                    siguiente pantalla.
                </p>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('transfusionChecklists.index', $stay) }}"
               class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-play-circle"></i> Iniciar transfusión
            </button>
        </div>
    </form>
</div>
@endsection
