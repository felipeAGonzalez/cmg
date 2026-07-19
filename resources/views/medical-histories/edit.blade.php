@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('stays.show', ['room' => $stay->room_id]) }}"
           class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div>
            <h2 class="mb-0">Historia Cl&iacute;nica</h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $patient->fullName() }}</strong>
                @if($patient->birth_date)
                    &middot; {{ $patient->birth_date->age }} a&ntilde;os
                @endif
                &middot; Cuarto {{ $stay->room->number ?? '—' }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Hay errores en el formulario:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">Datos del paciente (auto-rellenados)</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 small">
                <div class="col-md-6">
                    <strong>Nombre:</strong> {{ $patient->fullName() }}
                </div>
                <div class="col-md-3">
                    <strong>Edad:</strong>
                    {{ $patient->birth_date ? $patient->birth_date->age . ' años' : '—' }}
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong>
                    {{ $patient->gender === 'M' ? 'Masculino' : ($patient->gender === 'F' ? 'Femenino' : '—') }}
                </div>
                <div class="col-md-6">
                    <strong>Fecha de nacimiento:</strong>
                    {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '—' }}
                </div>
                <div class="col-md-6">
                    <strong>Cuarto:</strong> {{ $stay->room->number ?? '—' }}
                </div>
                <div class="col-md-6">
                    <strong>Fecha de ingreso:</strong>
                    {{ $stay->admission_date ? $stay->admission_date->format('d/m/Y H:i') : '—' }}
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('medicalHistory.update', $stay) }}"
          id="medical-history-form">
        @csrf
        @method('PUT')

        {{-- Campo oculto para el modo activo --}}
        <input type="hidden" name="mode" id="modeInput"
               value="{{ old('mode', $history->mode ?? 'complete') }}">

        @if(!auth()->user()->isDoctor())
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">M&eacute;dico tratante</h6>
                </div>
                <div class="card-body">
                    <label class="form-label">
                        M&eacute;dico responsable <span class="text-danger">*</span>
                    </label>
                    <select name="attending_doctor_id" required
                            class="form-select @error('attending_doctor_id') is-invalid @enderror">
                        <option value="">Selecciona un m&eacute;dico...</option>
                        @foreach($availableDoctors as $doctor)
                            <option value="{{ $doctor->id }}"
                                {{ old('attending_doctor_id', $history->attending_doctor_id) == $doctor->id ? 'selected' : '' }}>
                                Dr(a). {{ $doctor->name }} {{ $doctor->last_name_one ?? '' }}
                                @if($doctor->specialtiesLabel())
                                    — {{ $doctor->specialtiesLabel() }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('attending_doctor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif

        {{-- ================================================================ --}}
        {{-- TABS MODO COMPLETO / MODO SIMPLE                                 --}}
        {{-- ================================================================ --}}
        @php $activeMode = old('mode', $history->mode ?? 'complete'); @endphp

        <ul class="nav nav-tabs mb-3" id="historyModeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeMode === 'complete' ? 'active' : '' }}"
                        id="tab-complete-btn"
                        data-bs-toggle="tab" data-bs-target="#modeComplete"
                        type="button" role="tab">
                    <i class="bi bi-file-text"></i> Modo completo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeMode === 'simple' ? 'active' : '' }}"
                        id="tab-simple-btn"
                        data-bs-toggle="tab" data-bs-target="#modeSimple"
                        type="button" role="tab">
                    <i class="bi bi-list-check"></i> Modo simple
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ============================================================ --}}
            {{-- TAB: MODO COMPLETO (sin cambios internos)                    --}}
            {{-- ============================================================ --}}
            <div class="tab-pane fade {{ $activeMode === 'complete' ? 'show active' : '' }}"
                 id="modeComplete" role="tabpanel">

                @if($templates->isNotEmpty())
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info-subtle">
                            <h6 class="mb-0">
                                <i class="bi bi-journal-text"></i> Cargar desde plantilla
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                Selecciona una plantilla para pre-llenar los campos vac&iacute;os.
                                El contenido ya capturado no se sobrescribe.
                            </p>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">Plantilla disponible</label>
                                    <select id="template-selector" class="form-select">
                                        <option value="">Selecciona una plantilla...</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}">
                                                {{ $template->name }}
                                                @if(auth()->user()->isAdmin() || auth()->user()->isNurse())
                                                    (de Dr(a). {{ $template->owner->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="load-template-btn"
                                            class="btn btn-info w-100" disabled>
                                        <i class="bi bi-download"></i> Cargar plantilla
                                    </button>
                                </div>
                            </div>
                            <div id="template-load-status" class="small text-muted mt-2"></div>
                        </div>
                    </div>
                @endif

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Secciones de la historia cl&iacute;nica</h6>
                        <small class="text-muted">
                            Todas las secciones son opcionales. Captura lo que aplique al paciente.
                        </small>
                    </div>
                    <div class="card-body">
                        @foreach($sections as $key => $section)
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>{{ $section['order'] }}. {{ $section['label'] }}</strong>
                                </label>
                                <textarea name="{{ $key }}" rows="6"
                                          data-section="{{ $key }}"
                                          placeholder="{{ $section['placeholder'] }}"
                                          class="form-control section-textarea @error($key) is-invalid @enderror"
                                          >{{ old($key, $history->{$key}) }}</textarea>
                                @error($key)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /modeComplete --}}

            {{-- ============================================================ --}}
            {{-- TAB: MODO SIMPLE                                              --}}
            {{-- ============================================================ --}}
            <div class="tab-pane fade {{ $activeMode === 'simple' ? 'show active' : '' }}"
                 id="modeSimple" role="tabpanel">

                {{-- 1. Interrogatorio --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>1. Interrogatorio</strong>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Tipo de interrogatorio</label>
                        <div class="d-flex gap-3">
                            @foreach(['directo' => 'Directo', 'indirecto' => 'Indirecto', 'diferido' => 'Diferido'] as $val => $lbl)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                           name="simple_interrogation_type"
                                           id="interr_{{ $val }}" value="{{ $val }}"
                                           {{ old('simple_interrogation_type', $history->simple_interrogation_type) === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="interr_{{ $val }}">{{ $lbl }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 2. Antecedentes heredofamiliares --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>2. Antecedentes heredofamiliares</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Padre</label>
                                <textarea name="simple_heredo_father" rows="3" class="form-control form-control-sm"
                                          placeholder="Enfermedades del padre...">{{ old('simple_heredo_father', $history->simple_heredo_father) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Madre</label>
                                <textarea name="simple_heredo_mother" rows="3" class="form-control form-control-sm"
                                          placeholder="Enfermedades de la madre...">{{ old('simple_heredo_mother', $history->simple_heredo_mother) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Otros</label>
                                <textarea name="simple_heredo_other" rows="3" class="form-control form-control-sm"
                                          placeholder="Hermanos, abuelos, tíos...">{{ old('simple_heredo_other', $history->simple_heredo_other) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Antecedentes personales no patológicos --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>3. Antecedentes personales no patol&oacute;gicos</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Originario de</label>
                                <input type="text" name="simple_origin" class="form-control form-control-sm"
                                       value="{{ old('simple_origin', $history->simple_origin) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Residente en</label>
                                <input type="text" name="simple_resident_of" class="form-control form-control-sm"
                                       value="{{ old('simple_resident_of', $history->simple_resident_of) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ocupaci&oacute;n</label>
                                <input type="text" name="simple_occupation" class="form-control form-control-sm"
                                       value="{{ old('simple_occupation', $history->simple_occupation) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Escolaridad</label>
                                <input type="text" name="simple_education" class="form-control form-control-sm"
                                       value="{{ old('simple_education', $history->simple_education) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Vivienda</label>
                                <select name="simple_housing_type" id="simpleHousingType"
                                        class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach(['propia' => 'Propia', 'rentada' => 'Rentada', 'otro' => 'Otro'] as $v => $l)
                                        <option value="{{ $v }}"
                                            {{ old('simple_housing_type', $history->simple_housing_type) === $v ? 'selected' : '' }}>
                                            {{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="simple_housing_other"
                                       id="simpleHousingOther"
                                       class="form-control form-control-sm mt-1"
                                       placeholder="Especificar..."
                                       value="{{ old('simple_housing_other', $history->simple_housing_other) }}"
                                       style="{{ old('simple_housing_type', $history->simple_housing_type) === 'otro' ? '' : 'display:none;' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Estado civil</label>
                                <select name="simple_marital_status" id="simpleMaritalStatus"
                                        class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach(['soltero' => 'Soltero/a', 'casado' => 'Casado/a', 'otro' => 'Otro'] as $v => $l)
                                        <option value="{{ $v }}"
                                            {{ old('simple_marital_status', $history->simple_marital_status) === $v ? 'selected' : '' }}>
                                            {{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="simple_marital_status_other"
                                       id="simpleMaritalOther"
                                       class="form-control form-control-sm mt-1"
                                       placeholder="Especificar..."
                                       value="{{ old('simple_marital_status_other', $history->simple_marital_status_other) }}"
                                       style="{{ old('simple_marital_status', $history->simple_marital_status) === 'otro' ? '' : 'display:none;' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Alimentaci&oacute;n</label>
                                <input type="text" name="simple_diet" class="form-control form-control-sm"
                                       value="{{ old('simple_diet', $history->simple_diet) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Religi&oacute;n</label>
                                <input type="text" name="simple_religion" class="form-control form-control-sm"
                                       value="{{ old('simple_religion', $history->simple_religion) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Grupo y Rh</label>
                                <input type="text" name="simple_blood_type_rh" class="form-control form-control-sm"
                                       placeholder="Ej. O+" value="{{ old('simple_blood_type_rh', $history->simple_blood_type_rh) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Higi&eacute;nicos</label>
                                <textarea name="simple_hygiene" rows="2" class="form-control form-control-sm"
                                          placeholder="Baño, higiene bucal, ropa...">{{ old('simple_hygiene', $history->simple_hygiene) }}</textarea>
                            </div>
                        </div>

                        {{-- Checkboxes no patológicos --}}
                        <label class="form-label fw-semibold">Otros antecedentes</label>
                        @foreach($simpleConfigs['nonPathologicalChecks'] as $key => $label)
                            @php
                                $item = old("simple_non_pathological_checks.$key",
                                    ($history->simple_non_pathological_checks[$key] ?? null));
                                $hasCondition = is_array($item) ? ($item['has_condition'] ?? false) : false;
                                $detail = is_array($item) ? ($item['detail'] ?? '') : '';
                            @endphp
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fw-semibold" style="min-width:160px;">{{ $label }}</span>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input simple-check-toggle"
                                               type="checkbox"
                                               name="simple_non_pathological_checks[{{ $key }}][has_condition]"
                                               id="np_{{ $key }}"
                                               value="1"
                                               data-detail="np_detail_{{ $key }}"
                                               {{ $hasCondition ? 'checked' : '' }}>
                                        <label class="form-check-label" for="np_{{ $key }}">Sí</label>
                                    </div>
                                </div>
                                <div id="np_detail_{{ $key }}" class="{{ $hasCondition ? '' : 'd-none' }} mt-2">
                                    <input type="text"
                                           name="simple_non_pathological_checks[{{ $key }}][detail]"
                                           class="form-control form-control-sm"
                                           placeholder="Detalle..."
                                           value="{{ $detail }}">
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Otros</label>
                            <textarea name="simple_non_pathological_other" rows="2" class="form-control form-control-sm">{{ old('simple_non_pathological_other', $history->simple_non_pathological_other) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 4. Antecedentes personales patológicos --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>4. Antecedentes personales patol&oacute;gicos</strong>
                    </div>
                    <div class="card-body">
                        @foreach($simpleConfigs['pathologicalChecks'] as $key => $label)
                            @php
                                $item = old("simple_pathological_checks.$key",
                                    ($history->simple_pathological_checks[$key] ?? null));
                                $hasCondition = is_array($item) ? ($item['has_condition'] ?? false) : false;
                                $detail = is_array($item) ? ($item['detail'] ?? '') : '';
                            @endphp
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fw-semibold" style="min-width:240px;">{{ $label }}</span>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input simple-check-toggle"
                                               type="checkbox"
                                               name="simple_pathological_checks[{{ $key }}][has_condition]"
                                               id="path_{{ $key }}"
                                               value="1"
                                               data-detail="path_detail_{{ $key }}"
                                               {{ $hasCondition ? 'checked' : '' }}>
                                        <label class="form-check-label" for="path_{{ $key }}">Sí</label>
                                    </div>
                                </div>
                                <div id="path_detail_{{ $key }}" class="{{ $hasCondition ? '' : 'd-none' }} mt-2">
                                    <input type="text"
                                           name="simple_pathological_checks[{{ $key }}][detail]"
                                           class="form-control form-control-sm"
                                           placeholder="Detalle..."
                                           value="{{ $detail }}">
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Otros antecedentes patol&oacute;gicos</label>
                            <textarea name="simple_pathological_other" rows="2" class="form-control form-control-sm">{{ old('simple_pathological_other', $history->simple_pathological_other) }}</textarea>
                        </div>
                        <div class="mt-3">
                            <label class="form-label fw-semibold">Antecedentes anest&eacute;sicos</label>
                            <textarea name="simple_anesthetics_history" rows="2" class="form-control form-control-sm"
                                      placeholder="Cirugías previas, reacciones a anestesia...">{{ old('simple_anesthetics_history', $history->simple_anesthetics_history) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 5. Antecedentes gineco-obstétricos --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>5. Antecedentes gineco-obst&eacute;tricos</strong>
                    </div>
                    <div class="card-body">
                        @php $gyneco = old('simple_gyneco_history', $history->simple_gyneco_history ?? []); @endphp
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Menarca</label>
                                <input type="text" name="simple_gyneco_history[menarche]" class="form-control form-control-sm"
                                       value="{{ $gyneco['menarche'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Ritmo</label>
                                <input type="text" name="simple_gyneco_history[rhythm]" class="form-control form-control-sm"
                                       placeholder="Ej. 28x5" value="{{ $gyneco['rhythm'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">FUM</label>
                                <input type="text" name="simple_gyneco_history[lmp]" class="form-control form-control-sm"
                                       value="{{ $gyneco['lmp'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">IVSA</label>
                                <input type="text" name="simple_gyneco_history[ivsa]" class="form-control form-control-sm"
                                       placeholder="Edad de inicio" value="{{ $gyneco['ivsa'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">N&uacute;mero de parejas</label>
                                <input type="number" name="simple_gyneco_history[partners]" class="form-control form-control-sm"
                                       min="0" value="{{ $gyneco['partners'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">ITS</label>
                                <input type="text" name="simple_gyneco_history[sti]" class="form-control form-control-sm"
                                       value="{{ $gyneco['sti'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">M&eacute;todo de planificaci&oacute;n</label>
                                <input type="text" name="simple_gyneco_history[contraception]" class="form-control form-control-sm"
                                       value="{{ $gyneco['contraception'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">TRH</label>
                                <input type="text" name="simple_gyneco_history[hrt]" class="form-control form-control-sm"
                                       placeholder="Terapia de reemplazo hormonal" value="{{ $gyneco['hrt'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Gestas</label>
                                <input type="number" name="simple_gyneco_history[gravida]" class="form-control form-control-sm"
                                       min="0" value="{{ $gyneco['gravida'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Partos</label>
                                <input type="number" name="simple_gyneco_history[para]" class="form-control form-control-sm"
                                       min="0" value="{{ $gyneco['para'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Ces&aacute;reas</label>
                                <input type="number" name="simple_gyneco_history[cesarean]" class="form-control form-control-sm"
                                       min="0" value="{{ $gyneco['cesarean'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Abortos</label>
                                <input type="number" name="simple_gyneco_history[abortion]" class="form-control form-control-sm"
                                       min="0" value="{{ $gyneco['abortion'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Ect&oacute;picos</label>
                                <input type="number" name="simple_gyneco_history[ectopic]" class="form-control form-control-sm"
                                       min="0" value="{{ $gyneco['ectopic'] ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Citolog&iacute;a cervical</label>
                                <input type="text" name="simple_gyneco_history[pap_smear]" class="form-control form-control-sm"
                                       placeholder="Fecha/resultado" value="{{ $gyneco['pap_smear'] ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mastograf&iacute;a</label>
                                <input type="text" name="simple_gyneco_history[mammography]" class="form-control form-control-sm"
                                       placeholder="Fecha/resultado" value="{{ $gyneco['mammography'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alteraciones menstruales / Otros</label>
                                <textarea name="simple_gyneco_history[alterations]" rows="2" class="form-control form-control-sm">{{ $gyneco['alterations'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Vacunas</label>
                            <div class="row g-2">
                                @foreach($simpleConfigs['gynecoVaccines'] as $vKey => $vLabel)
                                    @php $vChecked = !empty(old("simple_gyneco_vaccines.$vKey",
                                        ($history->simple_gyneco_vaccines[$vKey] ?? false))); @endphp
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="simple_gyneco_vaccines[{{ $vKey }}]"
                                                   id="vacc_{{ $vKey }}" value="1"
                                                   {{ $vChecked ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vacc_{{ $vKey }}">{{ $vLabel }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6. Padecimiento actual --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>6. Padecimiento actual</strong>
                    </div>
                    <div class="card-body">
                        <textarea name="simple_current_illness" rows="5" class="form-control"
                                  placeholder="Descripci&oacute;n cronol&oacute;gica del padecimiento actual...">{{ old('simple_current_illness', $history->simple_current_illness) }}</textarea>
                    </div>
                </div>

                {{-- 7. Revisión por aparatos y sistemas --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>7. Revisi&oacute;n por aparatos y sistemas</strong>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Describir s&iacute;ntomas positivos de cada sistema.</p>
                        <div class="row g-3">
                            @foreach($simpleConfigs['reviewOfSystems'] as $sKey => $sLabel)
                                @php $ros = old("simple_review_of_systems.$sKey",
                                    ($history->simple_review_of_systems[$sKey] ?? '')); @endphp
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">{{ $sLabel }}</label>
                                    <textarea name="simple_review_of_systems[{{ $sKey }}]"
                                              rows="2" class="form-control form-control-sm"
                                              placeholder="Sin alteraciones / Describir...">{{ $ros }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 8. Valoración de dolor --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>8. Valoraci&oacute;n de dolor</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Escala EVA
                                    <span class="text-muted fw-normal small">(0 = sin dolor, 10 = peor dolor)</span>
                                </label>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">0</span>
                                    <input type="range" name="simple_pain_eva_score" id="evaRange"
                                           class="form-range flex-grow-1"
                                           min="0" max="10" step="1"
                                           value="{{ old('simple_pain_eva_score', $history->simple_pain_eva_score ?? '') }}"
                                           oninput="document.getElementById('evaVal').textContent = this.value">
                                    <span class="small text-muted">10</span>
                                    <span class="badge bg-danger ms-2" id="evaVal"
                                          style="min-width:28px;">{{ old('simple_pain_eva_score', $history->simple_pain_eva_score ?? '—') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Escala Wong-Baker</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach([0 => 'Sin dolor', 2 => 'Duele poco', 4 => 'Duele más', 6 => 'Duele bastante', 8 => 'Duele mucho', 10 => 'Insoportable'] as $score => $desc)
                                        @php $wbCurrent = old('simple_pain_wongbaker_score', $history->simple_pain_wongbaker_score ?? null); @endphp
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="simple_pain_wongbaker_score"
                                                   id="wb_{{ $score }}" value="{{ $score }}"
                                                   {{ (string)$wbCurrent === (string)$score ? 'checked' : '' }}>
                                            <label class="form-check-label small text-center d-block" for="wb_{{ $score }}">
                                                <strong>{{ $score }}</strong><br>
                                                <span class="text-muted" style="font-size:0.7rem;">{{ $desc }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Tipo de dolor</label>
                                <select name="simple_pain_type" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach(['somatico' => 'Somático', 'visceral' => 'Visceral', 'neuropatico' => 'Neuropático'] as $v => $l)
                                        <option value="{{ $v }}"
                                            {{ old('simple_pain_type', $history->simple_pain_type) === $v ? 'selected' : '' }}>
                                            {{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Regi&oacute;n anat&oacute;mica</label>
                                <input type="text" name="simple_pain_region" class="form-control form-control-sm"
                                       value="{{ old('simple_pain_region', $history->simple_pain_region) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Duraci&oacute;n</label>
                                <select name="simple_pain_duration" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach(['continuo' => 'Continuo', 'intermitente' => 'Intermitente'] as $v => $l)
                                        <option value="{{ $v }}"
                                            {{ old('simple_pain_duration', $history->simple_pain_duration) === $v ? 'selected' : '' }}>
                                            {{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Signos asociados</label>
                                <div class="row g-2">
                                    @foreach($simpleConfigs['painSigns'] as $psKey => $psLabel)
                                        @php $psChecked = !empty(old("simple_pain_associated_signs.$psKey",
                                            ($history->simple_pain_associated_signs[$psKey] ?? false))); @endphp
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="simple_pain_associated_signs[{{ $psKey }}]"
                                                       id="pain_sign_{{ $psKey }}" value="1"
                                                       {{ $psChecked ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pain_sign_{{ $psKey }}">{{ $psLabel }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Factores objetivos / subjetivos asociados</label>
                                <textarea name="simple_pain_associated_factors" rows="2" class="form-control form-control-sm">{{ old('simple_pain_associated_factors', $history->simple_pain_associated_factors) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 9. Exploración física --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>9. Exploraci&oacute;n f&iacute;sica</strong>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Signos vitales</label>
                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label small">TA</label>
                                <input type="text" name="simple_exam_ta" class="form-control form-control-sm"
                                       placeholder="120/80" value="{{ old('simple_exam_ta', $history->simple_exam_ta) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Pulso</label>
                                <input type="text" name="simple_exam_pulse" class="form-control form-control-sm"
                                       value="{{ old('simple_exam_pulse', $history->simple_exam_pulse) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">FC</label>
                                <input type="text" name="simple_exam_fc" class="form-control form-control-sm"
                                       value="{{ old('simple_exam_fc', $history->simple_exam_fc) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">FR</label>
                                <input type="text" name="simple_exam_fr" class="form-control form-control-sm"
                                       value="{{ old('simple_exam_fr', $history->simple_exam_fr) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Temp</label>
                                <input type="text" name="simple_exam_temp" class="form-control form-control-sm"
                                       value="{{ old('simple_exam_temp', $history->simple_exam_temp) }}">
                            </div>
                        </div>

                        <label class="form-label fw-semibold">Exploraci&oacute;n por sistemas</label>
                        <div class="row g-3">
                            @foreach($simpleConfigs['examBySystem'] as $esKey => $esLabel)
                                @php $esVal = old("simple_exam_by_system.$esKey",
                                    ($history->simple_exam_by_system[$esKey] ?? '')); @endphp
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">{{ $esLabel }}</label>
                                    <textarea name="simple_exam_by_system[{{ $esKey }}]"
                                              rows="2" class="form-control form-control-sm"
                                              placeholder="Hallazgos...">{{ $esVal }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 10–13. Cierre --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>10. Estudios, diagn&oacute;stico, plan y pron&oacute;stico</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Estudios de laboratorio, gabinete y otros</label>
                            <textarea name="simple_lab_studies" rows="3" class="form-control">{{ old('simple_lab_studies', $history->simple_lab_studies) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Diagn&oacute;stico</label>
                            <textarea name="simple_diagnosis" rows="3" class="form-control">{{ old('simple_diagnosis', $history->simple_diagnosis) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Terap&eacute;utica empleada y resultados</label>
                            <textarea name="simple_therapeutics" rows="3" class="form-control">{{ old('simple_therapeutics', $history->simple_therapeutics) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pron&oacute;stico</label>
                            <textarea name="simple_prognosis" rows="2" class="form-control">{{ old('simple_prognosis', $history->simple_prognosis) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 14. Elaboración --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>14. Datos de elaboraci&oacute;n</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Fecha y hora de elaboraci&oacute;n</label>
                                <input type="datetime-local" name="simple_elaboration_datetime"
                                       class="form-control form-control-sm"
                                       value="{{ old('simple_elaboration_datetime',
                                           $history->simple_elaboration_datetime
                                               ? $history->simple_elaboration_datetime->format('Y-m-d\TH:i')
                                               : '') }}">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Elabor&oacute;</label>
                                <select name="elaborated_by_id" class="form-select form-select-sm">
                                    <option value="">— Mismo médico tratante —</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}"
                                            {{ old('elaborated_by_id', $history->elaborated_by_id) == $doc->id ? 'selected' : '' }}>
                                            Dr(a). {{ $doc->name }} {{ $doc->last_name_one ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /modeSimple --}}

        </div>{{-- /tab-content --}}

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('stays.show', ['room' => $stay->room_id]) }}" class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar historia cl&iacute;nica
            </button>
        </div>
    </form>
</div>

<script>
// ── Modo activo → actualiza campo oculto ──────────────────────────────────
document.querySelectorAll('#historyModeTabs button').forEach(function(btn) {
    btn.addEventListener('shown.bs.tab', function(e) {
        var target = e.target.getAttribute('data-bs-target');
        document.getElementById('modeInput').value = target === '#modeSimple' ? 'simple' : 'complete';
    });
});

// ── Checkboxes Sí/No → mostrar/ocultar detalle ───────────────────────────
document.querySelectorAll('.simple-check-toggle').forEach(function(chk) {
    chk.addEventListener('change', function() {
        var detailId = this.getAttribute('data-detail');
        var detailEl = document.getElementById(detailId);
        if (detailEl) detailEl.classList.toggle('d-none', !this.checked);
    });
});

// ── Vivienda / Estado civil → "Otro" condicional ──────────────────────────
function conditionalOther(selectId, otherId) {
    var sel = document.getElementById(selectId);
    var inp = document.getElementById(otherId);
    if (!sel || !inp) return;
    sel.addEventListener('change', function() {
        inp.style.display = this.value === 'otro' ? '' : 'none';
        if (this.value !== 'otro') inp.value = '';
    });
}
conditionalOther('simpleHousingType', 'simpleHousingOther');
conditionalOther('simpleMaritalStatus', 'simpleMaritalOther');

// ── Plantilla (Modo completo) ─────────────────────────────────────────────
(function() {
    var selector = document.getElementById('template-selector');
    var loadBtn  = document.getElementById('load-template-btn');
    var statusEl = document.getElementById('template-load-status');

    if (!selector || !loadBtn) return;

    selector.addEventListener('change', function() {
        loadBtn.disabled = !this.value;
    });

    loadBtn.addEventListener('click', function() {
        var templateId = selector.value;
        if (!templateId) return;

        statusEl.textContent = 'Cargando plantilla...';
        statusEl.className = 'small text-muted mt-2';
        loadBtn.disabled = true;

        fetch('/medical-history-templates/' + templateId + '/content', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('Error HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            var sections = data.sections || {};
            var filledCount = 0;
            var skippedCount = 0;

            Object.keys(sections).forEach(function(key) {
                var content = sections[key];
                var textarea = document.querySelector('textarea[data-section="' + key + '"]');
                if (!textarea) return;

                var currentValue = textarea.value.trim();
                if (currentValue === '' && content) {
                    textarea.value = content;
                    filledCount++;
                } else if (currentValue !== '' && content) {
                    skippedCount++;
                }
            });

            var msg = 'Plantilla cargada. ' + filledCount + ' secciones rellenadas.';
            if (skippedCount > 0) {
                msg += ' ' + skippedCount + ' secciones omitidas (ya tenían contenido).';
            }
            statusEl.textContent = msg;
            statusEl.className = 'small text-success mt-2';
            loadBtn.disabled = false;
        })
        .catch(function(err) {
            statusEl.textContent = 'Error al cargar la plantilla: ' + err.message;
            statusEl.className = 'small text-danger mt-2';
            loadBtn.disabled = false;
        });
    });
})();
</script>
@endsection
