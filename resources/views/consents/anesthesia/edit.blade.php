@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $backUrl = route('stays.show', ['room' => $stay->room, 'stay' => $stay->id]) . '#documents';
    $negationApplies = (bool) old('negation.applies', $formData['negation']['applies'] ?? false);
    $revocationApplies = (bool) old('revocation.applies', $formData['revocation']['applies'] ?? false);
@endphp
<div class="container py-4" style="max-width:860px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-file-earmark-medical me-2"></i>Consentimiento Informado para Anestesia
        </h4>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        Este consentimiento se llena cuando el paciente recibirá anestesia. Captura al anestesiólogo, el
        procedimiento y el tipo de anestesia. Las secciones de negación y revocación son opcionales.
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
            <div class="col-md-6"><span class="text-muted">Cuarto:</span> {{ $stay->room->number }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('anesthesiaConsent.update', $stay) }}">
        @csrf
        @method('PUT')

        @if($user->isDoctor())
            <input type="hidden" name="prescribed_by_id" value="{{ $user->id }}">
        @endif

        {{-- Médico anestesiólogo --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard-pulse me-1"></i>Médico anestesiólogo
            </h6>
            <div class="row g-3">
                @unless($user->isDoctor())
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
                @endunless
                <div class="col-md-6">
                    <label for="anesthesiologist_name" class="form-label fw-semibold">Nombre del anestesiólogo</label>
                    <input type="text" id="anesthesiologist_name" name="anesthesiologist_name" maxlength="200"
                           class="form-control @error('anesthesiologist_name') is-invalid @enderror"
                           value="{{ old('anesthesiologist_name', $formData['anesthesiologist_name'] ?? '') }}">
                    @error('anesthesiologist_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="anesthesiologist_state" class="form-label fw-semibold">Estado (autorización profesional)</label>
                    <input type="text" id="anesthesiologist_state" name="anesthesiologist_state" maxlength="100"
                           class="form-control @error('anesthesiologist_state') is-invalid @enderror"
                           value="{{ old('anesthesiologist_state', $formData['anesthesiologist_state'] ?? 'Guanajuato') }}">
                    @error('anesthesiologist_state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Datos del responsable --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-person-vcard me-1"></i>Datos del responsable
            </h6>
            <div class="alert alert-secondary py-2 small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Estos datos se rellenaron automáticamente desde el Consentimiento Autorizado. Edítalos si han cambiado.
            </div>
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
                           class="form-control @error('responsible_relationship') is-invalid @enderror"
                           value="{{ old('responsible_relationship', $formData['responsible_relationship'] ?? '') }}">
                    @error('responsible_relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="responsible_phone" class="form-label fw-semibold">Teléfono del representante <span class="text-muted">(opcional)</span></label>
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
            </div>
        </div>

        {{-- Datos del procedimiento --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-clipboard2-pulse me-1"></i>Datos del procedimiento
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <label for="procedure_name" class="form-label fw-semibold">Procedimiento diagnóstico y/o quirúrgico</label>
                    <input type="text" id="procedure_name" name="procedure_name" maxlength="500"
                           class="form-control @error('procedure_name') is-invalid @enderror"
                           value="{{ old('procedure_name', $formData['procedure_name'] ?? '') }}">
                    @error('procedure_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="anesthesia_type" class="form-label fw-semibold">Tipo de anestesia</label>
                    <input type="text" id="anesthesia_type" name="anesthesia_type" maxlength="200"
                           placeholder="Ej. General, Regional, Local"
                           class="form-control @error('anesthesia_type') is-invalid @enderror"
                           value="{{ old('anesthesia_type', $formData['anesthesia_type'] ?? '') }}">
                    @error('anesthesia_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold d-block">Carácter</label>
                    @php $character = old('anesthesia_character', $formData['anesthesia_character'] ?? 'elective'); @endphp
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="anesthesia_character" id="char_elective"
                               value="elective" {{ $character === 'elective' ? 'checked' : '' }}>
                        <label class="form-check-label" for="char_elective">Electivo</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="anesthesia_character" id="char_urgent"
                               value="urgent" {{ $character === 'urgent' ? 'checked' : '' }}>
                        <label class="form-check-label" for="char_urgent">Urgente</label>
                    </div>
                    @error('anesthesia_character')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Testigos --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                <i class="bi bi-people me-1"></i>Testigos
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="witness_1_name" class="form-label fw-semibold">Testigo 1</label>
                    <input type="text" id="witness_1_name" name="witness_1_name" maxlength="200"
                           class="form-control @error('witness_1_name') is-invalid @enderror"
                           value="{{ old('witness_1_name', $formData['witness_1_name'] ?? '') }}">
                    @error('witness_1_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="witness_2_name" class="form-label fw-semibold">Testigo 2 <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="witness_2_name" name="witness_2_name" maxlength="200"
                           class="form-control @error('witness_2_name') is-invalid @enderror"
                           value="{{ old('witness_2_name', $formData['witness_2_name'] ?? '') }}">
                    @error('witness_2_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Negación (opcional) --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="negation[applies]" id="negation_applies"
                       value="1" {{ $negationApplies ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="negation_applies">
                    Negación del consentimiento (opcional)
                </label>
            </div>
            <div class="form-text">
                Marca esta casilla si el paciente o representante niega el consentimiento. En el PDF aparecerá la
                sección de negación para firmar.
            </div>
        </div>

        {{-- Revocación (opcional) --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="revocation[applies]" id="revocation_applies"
                       value="1" {{ $revocationApplies ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="revocation_applies">
                    Revocación de consentimiento previo (opcional)
                </label>
            </div>
            <div class="form-text mb-3">
                Marca esta casilla para revocar un consentimiento dado anteriormente.
            </div>
            <div class="row g-3" id="revocationFields" style="{{ $revocationApplies ? '' : 'display:none;' }}">
                <div class="col-md-6">
                    <label for="revocation_original" class="form-label fw-semibold">Fecha del consentimiento original</label>
                    <input type="date" id="revocation_original" name="revocation[original_consent_date]"
                           class="form-control @error('revocation.original_consent_date') is-invalid @enderror"
                           value="{{ old('revocation.original_consent_date', $formData['revocation']['original_consent_date'] ?? '') }}">
                    @error('revocation.original_consent_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="revocation_date" class="form-label fw-semibold">Fecha de la revocación</label>
                    <input type="date" id="revocation_date" name="revocation[revocation_date]"
                           class="form-control @error('revocation.revocation_date') is-invalid @enderror"
                           value="{{ old('revocation.revocation_date', $formData['revocation']['revocation_date'] ?? '') }}">
                    @error('revocation.revocation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

<script>
    document.getElementById('revocation_applies')?.addEventListener('change', function () {
        document.getElementById('revocationFields').style.display = this.checked ? '' : 'none';
    });
</script>
@endsection
