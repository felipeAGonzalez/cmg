@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Encabezado --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('transfusionChecklists.index', $stay) }}"
           class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0">
                Transfusión #{{ $checklist->id }}
                <span class="badge {{ $checklist->statusBadgeClass() }} fs-6">
                    {{ $checklist->statusLabel() }}
                </span>
            </h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $patient->fullName() }}</strong>
                · Iniciada: {{ $checklist->started_at->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Datos del paciente (auto, no editables) --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Datos del paciente</h6>
        </div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6">
                    <strong>Nombre:</strong> {{ $patient->fullName() }}
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong> {{ ucfirst(strtolower($patient->gender ?? '—')) }}
                </div>
                <div class="col-md-3">
                    <strong>Edad:</strong>
                    @if($patient->birth_date)
                        {{ \Carbon\Carbon::parse($patient->birth_date)->age }} años
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}
                </div>
                <div class="col-md-3">
                    <strong>Expediente:</strong> {{ $patient->id }}
                </div>
                <div class="col-md-6">
                    <strong>Fecha de nacimiento:</strong>
                    {{ $patient->birth_date
                        ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y')
                        : '—' }}
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('transfusionChecklists.update', $checklist) }}">
        @csrf
        @method('PUT')

        {{-- Folio editable --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Folio</label>
                        <input type="text" name="folio" maxlength="50"
                               value="{{ old('folio', $checklist->folio) }}"
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 1: ENTRADA --}}
        <div class="card mb-3 border-primary" id="section-entry">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">1. ENTRADA <small>(antes de transfundir)</small></h5>
            </div>
            <div class="card-body">
                {{-- Confirmaciones iniciales --}}
                <div class="mb-4">
                    <p class="fw-bold">El médico y el personal de enfermería, con el paciente confirma:</p>
                    @php
                        $entryConfirms = [
                            'entry_identity_confirmed' => 'Su identidad',
                            'entry_indication_confirmed' => 'Indicación de la transfusión',
                            'entry_product_confirmed' => 'Producto a transfundir',
                            'entry_consent_confirmed' => 'Su consentimiento informado',
                        ];
                    @endphp
                    @foreach($entryConfirms as $field => $label)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}"
                                   class="form-check-input"
                                   {{ old($field, $checklist->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Vía única --}}
                <div class="mb-4">
                    <p class="fw-bold">¿La vía para transfundir es única?</p>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="entry_via_unique" value="1"
                               id="entry_via_unique" class="form-check-input"
                               {{ old('entry_via_unique', $checklist->entry_via_unique) ? 'checked' : '' }}>
                        <label class="form-check-label" for="entry_via_unique">Sí</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="entry_via_permeable" value="1"
                               id="entry_via_permeable" class="form-check-input"
                               {{ old('entry_via_permeable', $checklist->entry_via_permeable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="entry_via_permeable">Permeable</label>
                    </div>
                </div>

                {{-- Asepsia --}}
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="entry_asepsis_done" value="1"
                               id="entry_asepsis_done" class="form-check-input"
                               {{ old('entry_asepsis_done', $checklist->entry_asepsis_done) ? 'checked' : '' }}>
                        <label class="form-check-label" for="entry_asepsis_done">
                            Se realizó la asepsia de sitio
                        </label>
                    </div>
                </div>

                {{-- Control de seguridad --}}
                <div class="mb-4">
                    <p class="fw-bold">Se completó el control de la seguridad de la transfusión al revisar...</p>
                    @php
                        $entryChecks = [
                            'entry_check_flebotech' => 'Flebotech',
                            'entry_check_availability' => 'La disponibilidad de la sangre o hemoderivado',
                            'entry_check_transport' => 'El traslado adecuado del producto',
                            'entry_check_vitals' => 'Corrobora signos vitales previamente',
                        ];
                    @endphp
                    @foreach($entryChecks as $field => $label)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}" class="form-check-input"
                                   {{ old($field, $checklist->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Equipo --}}
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="entry_equipment_ok" value="1"
                               id="entry_equipment_ok" class="form-check-input"
                               {{ old('entry_equipment_ok', $checklist->entry_equipment_ok) ? 'checked' : '' }}>
                        <label class="form-check-label" for="entry_equipment_ok">
                            Se colocó y se comprobó que funcione correctamente el equipo para transfusión
                        </label>
                    </div>
                </div>

                {{-- Características del paciente --}}
                <div class="mb-3">
                    <p class="fw-bold">¿Tiene el paciente...?</p>

                    {{-- Alergias --}}
                    <div class="mb-3">
                        <label class="form-label">Alergias conocidas:</label>
                        <div>
                            @foreach(['no' => 'No', 'yes' => 'Sí'] as $val => $lbl)
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="entry_allergies" value="{{ $val }}"
                                           id="entry_allergies_{{ $val }}" class="form-check-input"
                                           {{ old('entry_allergies', $checklist->entry_allergies) === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="entry_allergies_{{ $val }}">
                                        {{ $lbl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="entry_allergies_detail" maxlength="500"
                               value="{{ old('entry_allergies_detail', $checklist->entry_allergies_detail) }}"
                               placeholder="¿Cuáles? (si aplica)" class="form-control mt-2">
                    </div>

                    {{-- Reacciones previas --}}
                    <div class="mb-3">
                        <label class="form-label">Antecedente de reacciones previas a transfusión:</label>
                        <div>
                            @foreach(['no' => 'No', 'yes_doctor_aware' => 'Sí, y el médico está enterado'] as $val => $lbl)
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="entry_previous_reactions" value="{{ $val }}"
                                           id="entry_previous_reactions_{{ $val }}" class="form-check-input"
                                           {{ old('entry_previous_reactions', $checklist->entry_previous_reactions) === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="entry_previous_reactions_{{ $val }}">
                                        {{ $lbl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Riesgo de hemorragia --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Riesgo de hemorragia (adultos &gt;500 ml, niños &gt;7ml/1kg):
                        </label>
                        <div>
                            @foreach(['no' => 'No', 'yes_with_access' => 'Sí, y están colocadas al menos dos vías intravenosas o un catéter venoso central'] as $val => $lbl)
                                <div class="form-check">
                                    <input type="radio" name="entry_bleeding_risk" value="{{ $val }}"
                                           id="entry_bleeding_risk_{{ $val }}" class="form-check-input"
                                           {{ old('entry_bleeding_risk', $checklist->entry_bleeding_risk) === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="entry_bleeding_risk_{{ $val }}">
                                        {{ $lbl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Hemoderivados --}}
                    <div class="mb-3">
                        <label class="form-label">Hemoderivados y soluciones disponibles:</label>
                        <div>
                            @foreach(['no' => 'No', 'yes_crossmatched' => 'Sí, y se ha realizado el cruce de sangre previamente'] as $val => $lbl)
                                <div class="form-check">
                                    <input type="radio" name="entry_blood_products_available" value="{{ $val }}"
                                           id="entry_blood_products_available_{{ $val }}" class="form-check-input"
                                           {{ old('entry_blood_products_available', $checklist->entry_blood_products_available) === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="entry_blood_products_available_{{ $val }}">
                                        {{ $lbl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: PAUSA --}}
        <div class="card mb-3 border-warning" id="section-pause">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">2. PAUSA <small>(justo antes de iniciar)</small></h5>
            </div>
            <div class="card-body">
                {{-- Presentación del equipo --}}
                <div class="mb-4">
                    <p class="fw-bold">Se verifica que el médico ó la enfermera se presente por su nombre y su función:</p>
                    @php
                        $pausePresent = [
                            'pause_doctor_on_duty_present' => 'Médico de guardia',
                            'pause_anesthesiologist_present' => 'Anestesiólogo',
                            'pause_nurse_present' => 'Personal de Enfermería',
                        ];
                    @endphp
                    @foreach($pausePresent as $field => $label)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}" class="form-check-input"
                                   {{ old($field, $checklist->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Confirmación verbal --}}
                <div class="mb-4">
                    <p class="fw-bold">Se confirma de manera verbal e individual, por el médico de guardia, el anestesiólogo o personal de enfermería:</p>
                    @php
                        $pauseVerify = [
                            'pause_identity_verified' => 'La identidad del paciente',
                            'pause_indication_verified' => 'Indicación de la transfusión',
                            'pause_access_verified' => 'Vía de acceso única y permeable',
                            'pause_product_verified' => 'Producto a transfundir',
                        ];
                    @endphp
                    @foreach($pauseVerify as $field => $label)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}" class="form-check-input"
                                   {{ old($field, $checklist->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Datos del producto --}}
                <div class="card mb-3 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Datos del producto</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Grupo</label>
                                <input type="text" name="product_group" maxlength="10"
                                       value="{{ old('product_group', $checklist->product_group) }}"
                                       placeholder="Ej. O, A, B, AB" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Factor RH</label>
                                <input type="text" name="product_rh_factor" maxlength="10"
                                       value="{{ old('product_rh_factor', $checklist->product_rh_factor) }}"
                                       placeholder="Ej. +, -" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">FOLIO</label>
                                <input type="text" name="product_folio" maxlength="50"
                                       value="{{ old('product_folio', $checklist->product_folio) }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cantidad</label>
                                <input type="text" name="product_quantity" maxlength="50"
                                       value="{{ old('product_quantity', $checklist->product_quantity) }}"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tipos de producto --}}
                <div class="card mb-3 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Tipo de producto y cantidad</h6>

                        <div class="mb-3">
                            <label class="form-label">Volumen total a transfundir (ml)</label>
                            <input type="number" step="0.1" name="product_volume_total" min="0" max="10000"
                                   value="{{ old('product_volume_total', $checklist->product_volume_total) }}"
                                   class="form-control">
                        </div>

                        @php
                            $productTypes = [
                                ['product_red_cells', 'product_red_cells_amount', 'Concentrado Eritrocitario'],
                                ['product_fresh_plasma', 'product_fresh_plasma_amount', 'Plasma fresco'],
                                ['product_platelet_concentrate', 'product_platelet_concentrate_amount', 'Concentrado plaquetario'],
                                ['product_cryoprecipitate', 'product_cryoprecipitate_amount', 'Crioprecipitado'],
                                ['product_factor_vii', 'product_factor_vii_amount', 'Factor VII'],
                                ['product_apheresis', 'product_apheresis_amount', 'Aféresis'],
                            ];
                        @endphp

                        @foreach($productTypes as [$checkField, $amountField, $label])
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="{{ $checkField }}" value="1"
                                               id="{{ $checkField }}" class="form-check-input"
                                               {{ old($checkField, $checklist->{$checkField}) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $checkField }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" step="0.1" name="{{ $amountField }}" min="0" max="10000"
                                           value="{{ old($amountField, $checklist->{$amountField}) }}"
                                           placeholder="Cantidad" class="form-control form-control-sm">
                                </div>
                            </div>
                        @endforeach

                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label">Otro:</label>
                                <input type="text" name="product_other" maxlength="200"
                                       value="{{ old('product_other', $checklist->product_other) }}"
                                       placeholder="Descripción" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cantidad:</label>
                                <input type="number" step="0.1" name="product_other_amount" min="0" max="10000"
                                       value="{{ old('product_other_amount', $checklist->product_other_amount) }}"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Signos vitales --}}
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Signos vitales (pre-transfusión)</h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">FC</label>
                                <input type="number" name="pause_vitals_fc" min="0" max="300"
                                       value="{{ old('pause_vitals_fc', $checklist->pause_vitals_fc) }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">TA</label>
                                <input type="text" name="pause_vitals_ta" maxlength="20"
                                       value="{{ old('pause_vitals_ta', $checklist->pause_vitals_ta) }}"
                                       placeholder="Ej. 120/80" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">TEMP</label>
                                <input type="number" step="0.1" name="pause_vitals_temp" min="25" max="45"
                                       value="{{ old('pause_vitals_temp', $checklist->pause_vitals_temp) }}"
                                       class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">FR</label>
                                <input type="number" name="pause_vitals_fr" min="0" max="100"
                                       value="{{ old('pause_vitals_fr', $checklist->pause_vitals_fr) }}"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: DURANTE Y SALIDA --}}
        <div class="card mb-3 border-success" id="section-exit">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">3. DURANTE Y SALIDA</h5>
            </div>
            <div class="card-body">
                {{-- Durante --}}
                <div class="mb-4">
                    <p class="fw-bold">Con el médico de guardia, el anestesiólogo o el personal de enfermería:</p>
                    @php
                        $duringChecks = [
                            'during_monitoring_done' => 'El responsable monitoriza al paciente durante la transfusión',
                            'during_vitals_monitored' => 'Signos vitales',
                            'during_adverse_reactions_monitored' => 'Reacciones adversas',
                            'during_duration_monitored' => 'Duración de la transfusión',
                            'during_via_permeability_monitored' => 'Permeabilidad de la vía',
                        ];
                    @endphp
                    @foreach($duringChecks as $field => $label)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}" class="form-check-input"
                                   {{ old($field, $checklist->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Al terminar --}}
                <div class="mb-4">
                    <p class="fw-bold">Al terminar la transfusión, el médico de guardia, el anestesiólogo y el personal de enfermería confirman:</p>
                    @php
                        $exitChecks = [
                            'exit_vitals_confirmed' => 'Signos vitales',
                            'exit_logbook_filled' => 'Llenado correcto de la libreta de transfusión',
                            'exit_bag_disposed' => 'Desecha la bolsa con el equipo de la sangre o hemoderivado en el contenedor de RPBI',
                        ];
                    @endphp
                    @foreach($exitChecks as $field => $label)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}" class="form-check-input"
                                   {{ old($field, $checklist->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Eventos adversos --}}
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Eventos adversos</h6>

                        <div class="mb-3">
                            <label class="form-label">¿Ocurrieron eventos adversos?</label>
                            <div class="form-check">
                                <input type="checkbox" name="adverse_events_occurred" value="1"
                                       id="adverse_events_occurred" class="form-check-input"
                                       {{ old('adverse_events_occurred', $checklist->adverse_events_occurred) ? 'checked' : '' }}>
                                <label class="form-check-label" for="adverse_events_occurred">Sí</label>
                            </div>
                            <input type="text" name="adverse_events_detail" maxlength="500"
                                   value="{{ old('adverse_events_detail', $checklist->adverse_events_detail) }}"
                                   placeholder="¿Cuál? (si aplica)" class="form-control mt-2">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">¿Se registró el evento adverso?</label>
                            <div class="form-check">
                                <input type="checkbox" name="adverse_events_registered" value="1"
                                       id="adverse_events_registered" class="form-check-input"
                                       {{ old('adverse_events_registered', $checklist->adverse_events_registered) ? 'checked' : '' }}>
                                <label class="form-check-label" for="adverse_events_registered">Sí</label>
                            </div>
                            <input type="text" name="adverse_events_register_location" maxlength="200"
                                   value="{{ old('adverse_events_register_location', $checklist->adverse_events_register_location) }}"
                                   placeholder="¿Dónde?" class="form-control mt-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Espaciador para que la barra fija no tape el último contenido --}}
        <div style="height:80px;">&nbsp;</div>

        {{-- ============================================ --}}
        {{-- Barra fija inferior con navegación + botones --}}
        {{-- ============================================ --}}
        <div class="position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg"
             style="z-index:1030; padding:10px 15px;">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    {{-- Navegación rápida entre secciones --}}
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <small class="text-muted me-2 d-none d-md-inline">Ir a:</small>
                        <a href="#section-entry" class="btn btn-sm btn-outline-primary section-link">
                            <i class="bi bi-1-circle"></i> ENTRADA
                        </a>
                        <a href="#section-pause" class="btn btn-sm btn-outline-warning section-link">
                            <i class="bi bi-2-circle"></i> PAUSA
                        </a>
                        <a href="#section-exit" class="btn btn-sm btn-outline-success section-link">
                            <i class="bi bi-3-circle"></i> SALIDA
                        </a>
                        @if(!$checklist->isFinalized())
                        {{-- Indicador de requisitos en tiempo real --}}
                        <span id="req-indicator" class="badge bg-secondary ms-2"
                              style="cursor:pointer;"
                              data-bs-toggle="modal" data-bs-target="#preflightModal">
                            <i class="bi bi-list-check"></i>
                            <span id="req-count">0/10</span> requisitos
                        </span>
                        @endif
                    </div>

                    {{-- Acciones --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('transfusionChecklists.index', $stay) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x"></i> Cancelar
                        </a>
                        <button type="submit" name="action" value="save"
                                class="btn btn-sm btn-primary">
                            <i class="bi bi-save"></i> Guardar progreso
                        </button>
                        @if(!$checklist->isFinalized())
                            <button type="button" id="btnFinalizar"
                                    class="btn btn-sm btn-success">
                                <i class="bi bi-check-circle"></i> Guardar y finalizar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modal de pre-vuelo para Guardar y finalizar --}}
@if(!$checklist->isFinalized())
<div class="modal fade" id="preflightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-list-check me-1"></i>Requisitos para finalizar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="preflightBody">
                {{-- Llenado por JavaScript --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="btnConfirmFinalizar"
                        class="btn btn-success btn-sm" disabled>
                    <i class="bi bi-check-circle me-1"></i>Confirmar y finalizar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
(function () {
    // ── Scroll suave entre secciones ──────────────────────────────────────
    document.querySelectorAll('.section-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.getElementById(this.getAttribute('href').substring(1));
            if (target) {
                window.scrollTo({
                    top: target.getBoundingClientRect().top + window.pageYOffset - 20,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ── Si hay alerta de warning/error, scroll al tope ───────────────────
    if (document.querySelector('.alert-warning, .alert-danger')) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Requisitos para finalizar (refleja canBeFinalized() server-side) ──
    const requirements = [
        { id: 'entry_identity_confirmed',  label: 'Confirmar identidad del paciente (ENTRADA)',          type: 'checkbox' },
        { id: 'entry_indication_confirmed', label: 'Confirmar indicación de la transfusión (ENTRADA)',   type: 'checkbox' },
        { id: 'entry_product_confirmed',   label: 'Confirmar producto a transfundir (ENTRADA)',          type: 'checkbox' },
        { id: 'entry_consent_confirmed',   label: 'Confirmar consentimiento informado (ENTRADA)',        type: 'checkbox' },
        { name: 'product_group',           label: 'Capturar grupo sanguíneo (PAUSA)',                    type: 'text' },
        { name: 'product_rh_factor',       label: 'Capturar factor RH (PAUSA)',                         type: 'text' },
        { name: 'product_folio',           label: 'Capturar folio del producto (PAUSA)',                 type: 'text' },
        { id: 'pause_identity_verified',   label: 'Verificar identidad del paciente (PAUSA)',            type: 'checkbox' },
        { id: 'during_monitoring_done',    label: 'Confirmar monitoreo durante la transfusión (SALIDA)', type: 'checkbox' },
        { id: 'exit_vitals_confirmed',     label: 'Confirmar signos vitales al terminar (SALIDA)',       type: 'checkbox' },
    ];

    function getEl(req) {
        return req.id
            ? document.getElementById(req.id)
            : document.querySelector('[name="' + req.name + '"]');
    }

    function isMet(req) {
        const el = getEl(req);
        if (!el) return false;
        return req.type === 'checkbox' ? el.checked : el.value.trim() !== '';
    }

    function updateIndicator() {
        const met   = requirements.filter(isMet).length;
        const total = requirements.length;
        const all   = met === total;

        const indicator = document.getElementById('req-indicator');
        const countEl   = document.getElementById('req-count');
        if (!indicator) return;

        countEl.textContent = met + '/' + total;
        indicator.className = 'badge ms-2 ' + (all ? 'bg-success' : met > 0 ? 'bg-warning text-dark' : 'bg-secondary');

        const btnFinalizar = document.getElementById('btnFinalizar');
        if (btnFinalizar) {
            btnFinalizar.classList.toggle('btn-success', all);
            btnFinalizar.classList.toggle('btn-outline-success', !all);
        }
    }

    function buildPreflightBody() {
        const body = document.getElementById('preflightBody');
        const confirmBtn = document.getElementById('btnConfirmFinalizar');
        if (!body) return;

        let html = '<ul class="list-unstyled mb-0">';
        let allMet = true;
        requirements.forEach(req => {
            const met = isMet(req);
            if (!met) allMet = false;
            html += '<li class="py-1">';
            html += met
                ? '<i class="bi bi-check-circle-fill text-success me-2"></i>'
                : '<i class="bi bi-x-circle-fill text-danger me-2"></i>';
            html += req.label + '</li>';
        });
        html += '</ul>';
        if (!allMet) {
            html += '<div class="alert alert-warning mt-3 mb-0 small">'
                  + '<i class="bi bi-exclamation-triangle me-1"></i>'
                  + 'Completa los campos marcados con <i class="bi bi-x-circle-fill text-danger"></i> antes de finalizar.'
                  + '</div>';
        }
        body.innerHTML = html;
        if (confirmBtn) confirmBtn.disabled = !allMet;
    }

    // Actualizar indicador en tiempo real al cambiar cualquier campo
    requirements.forEach(req => {
        const el = getEl(req);
        if (el) el.addEventListener(req.type === 'checkbox' ? 'change' : 'input', updateIndicator);
    });

    // Ejecutar al cargar
    updateIndicator();

    // Botón "Guardar y finalizar" → abre el modal de pre-vuelo
    const btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function () {
            buildPreflightBody();
            new bootstrap.Modal(document.getElementById('preflightModal')).show();
        });
    }

    // Botón "Confirmar y finalizar" dentro del modal → envía el form como save_and_finalize
    const btnConfirm = document.getElementById('btnConfirmFinalizar');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            // Crear un input hidden temporal y enviar el form
            const form = document.querySelector('form[action*="transfusion"]');
            const hidden = document.createElement('input');
            hidden.type  = 'hidden';
            hidden.name  = 'action';
            hidden.value = 'save_and_finalize';
            form.appendChild(hidden);
            form.submit();
        });
    }
})();
</script>

@endsection
