@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:480px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-key me-2"></i>Cambiar contraseña
        </h4>
    </div>

    @if(Auth::user()->must_change_password)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Debes cambiar tu contraseña antes de continuar.
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card border-0 shadow-sm p-4">
        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label fw-semibold">Contraseña actual</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror"
                       autocomplete="current-password" required autofocus>
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="new-password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirmar nueva contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" autocomplete="new-password" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar nueva contraseña
                </button>
                @if(!Auth::user()->must_change_password)
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">Cancelar</a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
