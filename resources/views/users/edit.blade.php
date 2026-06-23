@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:640px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-pencil me-2"></i>Editar usuario
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
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label fw-semibold">Nombre(s)</label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" maxlength="100" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="last_name_one" class="form-label fw-semibold">Primer apellido</label>
                    <input type="text" id="last_name_one" name="last_name_one"
                           class="form-control @error('last_name_one') is-invalid @enderror"
                           value="{{ old('last_name_one', $user->last_name_one) }}" maxlength="100" required>
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
                           value="{{ old('last_name_two', $user->last_name_two) }}" maxlength="100">
                    @error('last_name_two')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">
                        Nueva contraseña <span class="text-muted fw-normal">(dejar en blanco para no cambiar)</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" autocomplete="new-password">
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label fw-semibold">Rol</label>
                    <select id="role" name="role"
                            class="form-select @error('role') is-invalid @enderror"
                            onchange="toggleSpecialty()" required>
                        <option value="admin"  {{ old('role', $user->role) === 'admin'  ? 'selected' : '' }}>Administrador</option>
                        <option value="doctor" {{ old('role', $user->role) === 'doctor' ? 'selected' : '' }}>Médico</option>
                        <option value="nurse"  {{ old('role', $user->role) === 'nurse'  ? 'selected' : '' }}>Enfermero/a</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6" id="professionalLicenseWrapper"
                     style="display: {{ in_array(old('role', $user->role), ['doctor', 'nurse']) ? 'block' : 'none' }};">
                    <label for="professional_license" class="form-label fw-semibold">
                        Cédula profesional <span class="text-danger" id="licenseRequired">*</span>
                    </label>
                    <input type="text" id="professional_license" name="professional_license"
                           class="form-control @error('professional_license') is-invalid @enderror"
                           value="{{ old('professional_license', $user->professional_license) }}" maxlength="20"
                           placeholder="Ej. 12345678 o AB-12345">
                    @error('professional_license')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Aparecerá en la firma de todos los documentos clínicos.</small>
                </div>

                <div class="col-12" id="specialtyWrapper" style="display:none;">
                    <label class="form-label fw-semibold">Especialidades</label>
                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        @forelse($availableSpecialties as $specialty)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="specialty_ids[]"
                                       value="{{ $specialty->id }}"
                                       id="specialty-{{ $specialty->id }}"
                                       {{ in_array($specialty->id, old('specialty_ids', $userSpecialtyIds)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="specialty-{{ $specialty->id }}">
                                    {{ $specialty->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">
                                No hay especialidades activas. Crea una en la sección Especialidades.
                            </p>
                        @endforelse
                    </div>
                    <small class="text-muted">
                        Selecciona una o más especialidades. Solo aplica a usuarios con rol Médico.
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Guardar cambios
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSpecialty() {
    const role = document.getElementById('role').value;
    const specialtyWrapper = document.getElementById('specialtyWrapper');
    const licenseWrapper = document.getElementById('professionalLicenseWrapper');
    const licenseInput = document.getElementById('professional_license');

    if (role === 'doctor') {
        specialtyWrapper.style.display = 'block';
    } else {
        specialtyWrapper.style.display = 'none';
        specialtyWrapper.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
    }

    if (role === 'doctor' || role === 'nurse') {
        licenseWrapper.style.display = 'block';
        licenseInput.setAttribute('required', 'required');
    } else {
        licenseWrapper.style.display = 'none';
        licenseInput.removeAttribute('required');
    }
}
toggleSpecialty();
</script>
@endpush
