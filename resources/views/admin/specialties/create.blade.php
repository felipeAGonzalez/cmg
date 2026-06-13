@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:560px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('specialties.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-mortarboard me-2"></i>Nueva especialidad
        </h4>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="card border-0 shadow-sm p-4">
        <form method="POST" action="{{ route('specialties.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Nombre de la especialidad</label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" maxlength="100" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Crear especialidad
                </button>
                <a href="{{ route('specialties.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
