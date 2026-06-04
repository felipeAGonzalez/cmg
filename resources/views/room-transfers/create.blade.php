@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:560px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('stays.show', $stay->room) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-arrow-left-right me-2"></i>Trasladar paciente
        </h4>
    </div>

    {{-- Info del paciente (lectura) --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
            <i class="bi bi-person me-1"></i>Paciente
        </h6>
        <div class="mb-1">
            <span class="text-muted small">Nombre</span>
            <div class="fw-semibold">{{ $stay->patient->fullName() }}</div>
        </div>
        <div>
            <span class="text-muted small">Cuarto actual</span>
            <div class="fw-semibold">Cuarto {{ $stay->room->number }}</div>
        </div>
        <div class="mt-2">
            <span class="text-muted small">Diagnóstico</span>
            <div class="fw-semibold" style="white-space:pre-line;">{{ Str::limit($stay->diagnosis, 200) }}</div>
        </div>
    </div>

    {{-- Formulario de traslado --}}
    <div class="card border-0 shadow-sm p-4">
        @if($availableRooms->isEmpty())
            <div class="alert alert-warning border-0 mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                No hay cuartos disponibles para el traslado en este momento.
            </div>
        @else
        <form method="POST" action="{{ route('roomTransfers.store', $stay) }}">
            @csrf

            <div class="mb-4">
                <label for="to_room_id" class="form-label fw-semibold">Trasladar al cuarto</label>
                <select id="to_room_id" name="to_room_id"
                        class="form-select @error('to_room_id') is-invalid @enderror" required>
                    <option value="">— Selecciona el cuarto destino —</option>
                    @foreach($availableRooms as $room)
                    <option value="{{ $room->id }}" {{ old('to_room_id') == $room->id ? 'selected' : '' }}>
                        Cuarto {{ $room->number }} — Disponible
                    </option>
                    @endforeach
                </select>
                @error('to_room_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('¿Confirmas el traslado de {{ addslashes($stay->patient->fullName()) }}?')">
                    <i class="bi bi-arrow-left-right me-1"></i>Confirmar traslado
                </button>
                <a href="{{ route('stays.show', $stay->room) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
