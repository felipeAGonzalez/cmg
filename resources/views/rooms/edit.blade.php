@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:480px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">Editar Cuarto {{ $room->number }}</h4>
    </div>

    <div class="card border-0 shadow-sm p-4">
        <form method="POST" action="{{ route('rooms.update', $room) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="number" class="form-label fw-semibold">Número de cuarto</label>
                <input type="number" id="number" name="number" min="1"
                       class="form-control @error('number') is-invalid @enderror"
                       value="{{ old('number', $room->number) }}" required autofocus>
                @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar cambios
                </button>
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
