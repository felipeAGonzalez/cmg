@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-person-plus me-2"></i>Ingresar paciente — Cuarto {{ $room->number }}
        </h4>
    </div>

    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="bi bi-info-circle me-1"></i>
        Si el paciente ya está registrado (mismo nombre completo y fecha de nacimiento), se reutilizará su expediente.
        No se permite ingresar a un paciente que ya tiene una estancia activa en otro cuarto.
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

    <form method="POST" action="{{ route('stays.store', $room) }}">
        @csrf

        {{-- Datos del paciente --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-person me-1"></i>Datos del paciente
            </h6>

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="name" class="form-label fw-semibold">Nombre(s)</label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" maxlength="100" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="last_name_one" class="form-label fw-semibold">Primer apellido</label>
                    <input type="text" id="last_name_one" name="last_name_one"
                           class="form-control @error('last_name_one') is-invalid @enderror"
                           value="{{ old('last_name_one') }}" maxlength="100" required>
                    @error('last_name_one')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="last_name_two" class="form-label fw-semibold">
                        Segundo apellido <span class="text-muted fw-normal">(opcional)</span>
                    </label>
                    <input type="text" id="last_name_two" name="last_name_two"
                           class="form-control @error('last_name_two') is-invalid @enderror"
                           value="{{ old('last_name_two') }}" maxlength="100">
                    @error('last_name_two')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="birth_date" class="form-label fw-semibold">Fecha de nacimiento</label>
                    <input type="date" id="birth_date" name="birth_date"
                           class="form-control @error('birth_date') is-invalid @enderror"
                           value="{{ old('birth_date') }}" required>
                    @error('birth_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold d-block">Género</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input @error('gender') is-invalid @enderror"
                               type="radio" name="gender" id="genderM" value="M"
                               {{ old('gender') === 'M' ? 'checked' : '' }}>
                        <label class="form-check-label" for="genderM">Masculino</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input @error('gender') is-invalid @enderror"
                               type="radio" name="gender" id="genderF" value="F"
                               {{ old('gender') === 'F' ? 'checked' : '' }}>
                        <label class="form-check-label" for="genderF">Femenino</label>
                    </div>
                    @error('gender')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Datos de la estancia --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Datos de la estancia
            </h6>

            <div class="mb-3">
                <label for="diagnosis" class="form-label fw-semibold">Diagnóstico</label>
                <textarea id="diagnosis" name="diagnosis" rows="4" maxlength="2000"
                          class="form-control @error('diagnosis') is-invalid @enderror"
                          required>{{ old('diagnosis') }}</textarea>
                @error('diagnosis')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="admission_date" class="form-label fw-semibold">Fecha y hora de ingreso</label>
                <input type="datetime-local" id="admission_date" name="admission_date"
                       class="form-control @error('admission_date') is-invalid @enderror"
                       value="{{ old('admission_date', now()->format('Y-m-d\TH:i')) }}" required>
                <div class="form-text">
                    <i class="bi bi-info-circle"></i>
                    Puede ajustar la fecha si se trata de un registro retrasado (emergencia nocturna, etc.).
                </div>
                @error('admission_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-person-check me-1"></i>Ingresar paciente
            </button>
            <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
