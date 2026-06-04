@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:640px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-person-plus me-2"></i>Nuevo paciente
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

    <div class="card border-0 shadow-sm p-4">
        <form method="POST" action="{{ route('patients.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label fw-semibold">Nombre(s)</label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" maxlength="100" required autofocus>
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

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar
                </button>
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
