@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:820px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('stays.show', ['room' => $stay->room, 'stay' => $stay->id]) }}#documents"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-file-earmark-text me-2"></i>Hoja Frontal — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    {{-- Datos automáticos (solo lectura) --}}
    <div class="card border-0 shadow-sm p-4 mb-4 bg-light">
        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
            <i class="bi bi-info-circle me-1"></i>Datos tomados del expediente (no editables aquí)
        </h6>
        <div class="row g-2 small">
            <div class="col-md-6"><span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong></div>
            <div class="col-md-6"><span class="text-muted">Sexo:</span> {{ $stay->patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</div>
            <div class="col-md-6"><span class="text-muted">Fecha de nacimiento:</span> {{ $stay->patient->birth_date->format('d/m/Y') }}</div>
            <div class="col-md-6"><span class="text-muted">Fecha de ingreso:</span> {{ $stay->admission_date->format('d/m/Y H:i') }}</div>
            <div class="col-12"><span class="text-muted">Diagnóstico de ingreso:</span> {{ $stay->diagnosis }}</div>
        </div>
        <div class="form-text mt-2">
            <i class="bi bi-lightbulb"></i>
            Estos datos se imprimen en vivo en el PDF; si cambian en el expediente, el PDF se actualiza automáticamente.
        </div>
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

    <form method="POST" action="{{ route('frontSheet.update', $stay) }}">
        @csrf
        @method('PUT')

        {{-- Datos administrativos --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard me-1"></i>Datos administrativos
            </h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="service" class="form-label fw-semibold">Servicio</label>
                    <select id="service" name="service"
                            class="form-select @error('service') is-invalid @enderror" required>
                        <option value="">— Selecciona —</option>
                        @foreach($services as $value => $label)
                            <option value="{{ $value }}" {{ old('service', $formData['service'] ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                        <option value="other" {{ old('service', $formData['service'] ?? '') === 'other' ? 'selected' : '' }}>
                            Otro…
                        </option>
                    </select>
                    @error('service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6" id="serviceOtherWrapper" style="display:none;">
                    <label for="service_other" class="form-label fw-semibold">Especifica el servicio</label>
                    <input type="text" id="service_other" name="service_other" maxlength="120"
                           class="form-control @error('service_other') is-invalid @enderror"
                           value="{{ old('service_other', $formData['service_other'] ?? '') }}">
                    @error('service_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="marital_status" class="form-label fw-semibold">Estado civil</label>
                    <select id="marital_status" name="marital_status"
                            class="form-select @error('marital_status') is-invalid @enderror">
                        <option value="">— Sin especificar —</option>
                        @foreach($maritalStatuses as $value => $label)
                            <option value="{{ $value }}" {{ old('marital_status', $formData['marital_status'] ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="occupation" class="form-label fw-semibold">Ocupación</label>
                    <input type="text" id="occupation" name="occupation" maxlength="120"
                           class="form-control @error('occupation') is-invalid @enderror"
                           value="{{ old('occupation', $formData['occupation'] ?? '') }}">
                    @error('occupation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Domicilio y contacto --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-geo-alt me-1"></i>Domicilio y contacto
            </h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="city" class="form-label fw-semibold">Ciudad</label>
                    <input type="text" id="city" name="city" maxlength="120"
                           class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $formData['city'] ?? '') }}">
                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="state" class="form-label fw-semibold">Estado</label>
                    <select id="state" name="state"
                            class="form-select @error('state') is-invalid @enderror">
                        <option value="">— Sin especificar —</option>
                        @foreach($states as $value => $label)
                            <option value="{{ $value }}" {{ old('state', $formData['state'] ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Domicilio</label>
                    <input type="text" id="address" name="address" maxlength="255"
                           class="form-control @error('address') is-invalid @enderror"
                           value="{{ old('address', $formData['address'] ?? '') }}">
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Teléfono</label>
                    <input type="text" id="phone" name="phone" maxlength="30"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $formData['phone'] ?? '') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Diagnósticos finales --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Diagnósticos finales
            </h6>
            <textarea id="final_diagnoses" name="final_diagnoses" rows="4" maxlength="3000"
                      class="form-control @error('final_diagnoses') is-invalid @enderror"
                      placeholder="Se llenan al egreso del paciente (opcional).">{{ old('final_diagnoses', $formData['final_diagnoses'] ?? '') }}</textarea>
            @error('final_diagnoses')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Guardar Hoja Frontal
            </button>
            <a href="{{ route('stays.show', ['room' => $stay->room, 'stay' => $stay->id]) }}#documents"
               class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const service = document.getElementById('service');
        const wrapper = document.getElementById('serviceOtherWrapper');

        function toggle() {
            wrapper.style.display = service.value === 'other' ? 'block' : 'none';
        }

        service.addEventListener('change', toggle);
        toggle();
    })();
</script>
@endpush
