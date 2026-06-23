@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;
    $months = collect(range(1, 12))->map(fn ($m) => Carbon::create(null, $m, 1)->translatedFormat('F'));
    $user = auth()->user();
    $backUrl = route('stays.show', ['room' => $stay->room, 'stay' => $stay->id]) . '#documents';
@endphp
<div class="container py-4" style="max-width:860px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-file-earmark-check me-2"></i>Consentimiento Autorizado Bajo Información
        </h4>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        Este consentimiento se llena al ingreso del paciente y autoriza al personal médico para realizar
        procedimientos diagnósticos, terapéuticos y quirúrgicos necesarios. Los datos del paciente se rellenan
        automáticamente; los datos del responsable y los testigos deben capturarse.
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Datos del paciente (solo lectura) --}}
    <div class="card border-0 shadow-sm p-4 mb-4 bg-light">
        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
            <i class="bi bi-person me-1"></i>Datos del paciente (tomados del expediente)
        </h6>
        <div class="row g-2 small">
            <div class="col-md-6"><span class="text-muted">Nombre:</span> <strong>{{ $stay->patient->fullName() }}</strong></div>
            <div class="col-md-3"><span class="text-muted">Sexo:</span> {{ $stay->patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</div>
            <div class="col-md-3"><span class="text-muted">Edad:</span> {{ $stay->patient->age() }} años</div>
            <div class="col-md-6"><span class="text-muted">Fecha de nacimiento:</span> {{ $stay->patient->birth_date->format('d/m/Y') }}</div>
            <div class="col-md-6"><span class="text-muted">Cuarto:</span> {{ $stay->room->number }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('authorizedConsent.update', $stay) }}">
        @csrf
        @method('PUT')

        {{-- Médico responsable --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard-pulse me-1"></i>Médico responsable
            </h6>
            <div class="row g-3">
                @if($user->isDoctor())
                    <input type="hidden" name="prescribed_by_id" value="{{ $user->id }}">
                    <div class="col-12">
                        <div class="form-text">Prescrita por: <strong>Dr(a). {{ $user->fullName() }}</strong></div>
                    </div>
                @else
                    <div class="col-md-6">
                        <label for="prescribed_by_id" class="form-label fw-semibold">Médico responsable</label>
                        <select id="prescribed_by_id" name="prescribed_by_id"
                                class="form-select @error('prescribed_by_id') is-invalid @enderror" required>
                            <option value="">— Selecciona —</option>
                            @foreach($availableDoctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (string) old('prescribed_by_id', $formData['prescribed_by_id'] ?? '') === (string) $doctor->id ? 'selected' : '' }}>
                                    Dr(a). {{ $doctor->fullName() }}
                                </option>
                            @endforeach
                        </select>
                        @error('prescribed_by_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif
                <div class="col-md-6">
                    <label for="doctor_name" class="form-label fw-semibold">Nombre del médico</label>
                    <input type="text" id="doctor_name" name="doctor_name" maxlength="200"
                           class="form-control @error('doctor_name') is-invalid @enderror"
                           value="{{ old('doctor_name', $formData['doctor_name'] ?? '') }}">
                    @error('doctor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="folio" class="form-label fw-semibold">Folio <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="folio" name="folio" maxlength="50"
                           class="form-control @error('folio') is-invalid @enderror"
                           value="{{ old('folio', $formData['folio'] ?? '') }}">
                    @error('folio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Datos del responsable --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-person-vcard me-1"></i>Datos del responsable
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="responsible_name" class="form-label fw-semibold">Nombre del responsable</label>
                    <input type="text" id="responsible_name" name="responsible_name" maxlength="200"
                           class="form-control @error('responsible_name') is-invalid @enderror"
                           value="{{ old('responsible_name', $formData['responsible_name'] ?? '') }}">
                    @error('responsible_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="responsible_relationship" class="form-label fw-semibold">Parentesco / relación</label>
                    <input type="text" id="responsible_relationship" name="responsible_relationship" maxlength="100"
                           placeholder="Ej. Esposa, Hijo"
                           class="form-control @error('responsible_relationship') is-invalid @enderror"
                           value="{{ old('responsible_relationship', $formData['responsible_relationship'] ?? '') }}">
                    @error('responsible_relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="responsible_phone" class="form-label fw-semibold">Teléfono <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="responsible_phone" name="responsible_phone" maxlength="30"
                           class="form-control @error('responsible_phone') is-invalid @enderror"
                           value="{{ old('responsible_phone', $formData['responsible_phone'] ?? '') }}">
                    @error('responsible_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="patient_phone" class="form-label fw-semibold">Teléfono del paciente <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="patient_phone" name="patient_phone" maxlength="30"
                           class="form-control @error('patient_phone') is-invalid @enderror"
                           value="{{ old('patient_phone', $formData['patient_phone'] ?? '') }}">
                    @error('patient_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="responsible_address" class="form-label fw-semibold">Domicilio <span class="text-muted">(opcional)</span></label>
                    <textarea id="responsible_address" name="responsible_address" rows="2" maxlength="500"
                              class="form-control @error('responsible_address') is-invalid @enderror">{{ old('responsible_address', $formData['responsible_address'] ?? '') }}</textarea>
                    @error('responsible_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="designated_person" class="form-label fw-semibold">
                        Persona designada para recibir información <span class="text-muted">(opcional)</span>
                    </label>
                    <input type="text" id="designated_person" name="designated_person" maxlength="200"
                           placeholder="Persona que recibirá información en caso de no poder decidir"
                           class="form-control @error('designated_person') is-invalid @enderror"
                           value="{{ old('designated_person', $formData['designated_person'] ?? '') }}">
                    @error('designated_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Datos clínicos --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Datos clínicos
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="diagnoses_0" class="form-label fw-semibold">Diagnóstico 1</label>
                    <input type="text" id="diagnoses_0" name="diagnoses[0]" maxlength="500"
                           class="form-control @error('diagnoses.0') is-invalid @enderror"
                           value="{{ old('diagnoses.0', $formData['diagnoses'][0] ?? '') }}">
                    @error('diagnoses.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="diagnoses_1" class="form-label fw-semibold">Diagnóstico 2</label>
                    <input type="text" id="diagnoses_1" name="diagnoses[1]" maxlength="500"
                           class="form-control @error('diagnoses.1') is-invalid @enderror"
                           value="{{ old('diagnoses.1', $formData['diagnoses'][1] ?? '') }}">
                    @error('diagnoses.1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="surgical_procedure" class="form-label fw-semibold">Procedimiento quirúrgico <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="surgical_procedure" name="surgical_procedure" maxlength="500"
                           class="form-control @error('surgical_procedure') is-invalid @enderror"
                           value="{{ old('surgical_procedure', $formData['surgical_procedure'] ?? '') }}">
                    @error('surgical_procedure')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="invasive_procedure" class="form-label fw-semibold">Procedimiento invasivo <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="invasive_procedure" name="invasive_procedure" maxlength="500"
                           class="form-control @error('invasive_procedure') is-invalid @enderror"
                           value="{{ old('invasive_procedure', $formData['invasive_procedure'] ?? '') }}">
                    @error('invasive_procedure')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Beneficios</label>
                    @for($i = 0; $i < 3; $i++)
                        <input type="text" name="benefits[{{ $i }}]" maxlength="500"
                               class="form-control mb-2 @error('benefits.'.$i) is-invalid @enderror"
                               placeholder="Beneficio {{ $i + 1 }}"
                               value="{{ old('benefits.'.$i, $formData['benefits'][$i] ?? '') }}">
                    @endfor
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Riesgos o complicaciones</label>
                    @for($i = 0; $i < 3; $i++)
                        <input type="text" name="risks[{{ $i }}]" maxlength="500"
                               class="form-control mb-2 @error('risks.'.$i) is-invalid @enderror"
                               placeholder="Riesgo {{ $i + 1 }}"
                               value="{{ old('risks.'.$i, $formData['risks'][$i] ?? '') }}">
                    @endfor
                </div>
                <div class="col-12">
                    <label for="alternatives" class="form-label fw-semibold">Alternativas <span class="text-muted">(opcional)</span></label>
                    <textarea id="alternatives" name="alternatives" rows="2" maxlength="1000"
                              class="form-control @error('alternatives') is-invalid @enderror">{{ old('alternatives', $formData['alternatives'] ?? '') }}</textarea>
                    @error('alternatives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Datos de firma --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-pen me-1"></i>Datos de firma
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="city" class="form-label fw-semibold">Ciudad</label>
                    <input type="text" id="city" name="city" maxlength="100"
                           class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $formData['city'] ?? 'Acámbaro, Gto.') }}">
                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label for="signed_day" class="form-label fw-semibold">Día</label>
                    <input type="number" id="signed_day" name="signed_day" min="1" max="31"
                           class="form-control @error('signed_day') is-invalid @enderror"
                           value="{{ old('signed_day', $formData['signed_day'] ?? now()->day) }}">
                    @error('signed_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="signed_month" class="form-label fw-semibold">Mes</label>
                    <select id="signed_month" name="signed_month"
                            class="form-select @error('signed_month') is-invalid @enderror">
                        @foreach($months as $month)
                            <option value="{{ $month }}" {{ old('signed_month', $formData['signed_month'] ?? '') === $month ? 'selected' : '' }}>
                                {{ ucfirst($month) }}
                            </option>
                        @endforeach
                    </select>
                    @error('signed_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="signed_year" class="form-label fw-semibold">Año</label>
                    <input type="number" id="signed_year" name="signed_year" min="2020" max="2100"
                           class="form-control @error('signed_year') is-invalid @enderror"
                           value="{{ old('signed_year', $formData['signed_year'] ?? now()->year) }}">
                    @error('signed_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="signed_time" class="form-label fw-semibold">Hora</label>
                    <input type="time" id="signed_time" name="signed_time"
                           class="form-control @error('signed_time') is-invalid @enderror"
                           value="{{ old('signed_time', $formData['signed_time'] ?? now()->format('H:i')) }}">
                    @error('signed_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="witness_1_name" class="form-label fw-semibold">Testigo 1</label>
                    <input type="text" id="witness_1_name" name="witness_1_name" maxlength="200"
                           class="form-control @error('witness_1_name') is-invalid @enderror"
                           value="{{ old('witness_1_name', $formData['witness_1_name'] ?? '') }}">
                    @error('witness_1_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="witness_2_name" class="form-label fw-semibold">Testigo 2</label>
                    <input type="text" id="witness_2_name" name="witness_2_name" maxlength="200"
                           class="form-control @error('witness_2_name') is-invalid @enderror"
                           value="{{ old('witness_2_name', $formData['witness_2_name'] ?? '') }}">
                    @error('witness_2_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle me-1"></i>Guardar y firmar
            </button>
        </div>
    </form>
</div>
@endsection
