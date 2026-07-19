@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('anesthesiaNotes.show', $note) }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="mb-0"><i class="bi bi-lungs"></i> Editar Nota de Anestesia #{{ $note->id }}</h2>
            <p class="text-muted mb-0 small">
                {{ $patient->fullName() }} — Estancia #{{ $stay->id }}
                @if($stay->room) — Hab. {{ $stay->room->number }} @endif
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $orSurgeonIsOther  = is_null($note->or_surgeon_user_id)  && !empty($note->or_surgeon_other_name);
        $orAssistantIsOther = is_null($note->or_assistant_user_id) && !empty($note->or_assistant_other_name);
    @endphp

    <form method="POST" action="{{ route('anesthesiaNotes.update', $note) }}">
        @csrf
        @method('PUT')

        {{-- Médico tratante (si no es doctor) --}}
        @if(!auth()->user()->isDoctor())
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold">Médico tratante (Anestesiólogo) <span class="text-danger">*</span></label>
                    <select name="attending_doctor_id" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($availableDoctors as $doc)
                            <option value="{{ $doc->id }}"
                                {{ old('attending_doctor_id', $note->attending_doctor_id) == $doc->id ? 'selected' : '' }}>
                                Dr(a). {{ $doc->name }} {{ $doc->last_name_one }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        {{-- Vínculo con Nota Postquirúrgica --}}
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label fw-semibold">Vincular a Nota Postquirúrgica (opcional)</label>
                <select name="post_surgical_note_id" class="form-select" id="postSurgicalNoteSelect">
                    <option value="">— Sin vincular —</option>
                    @foreach($availablePostSurgicalNotes as $psn)
                        <option value="{{ $psn->id }}"
                                data-surgeon="{{ $psn->surgeon_user_id }}"
                                data-surgeon-other="{{ $psn->surgeon_other_name }}"
                                data-assistant="{{ $psn->assistant_user_id }}"
                                data-assistant-other="{{ $psn->assistant_other_name }}"
                                {{ old('post_surgical_note_id', $note->post_surgical_note_id) == $psn->id ? 'selected' : '' }}>
                            Cirugía {{ $psn->surgery_date?->format('d/m/Y') }} —
                            {{ $psn->performed_surgery ? \Illuminate\Support\Str::limit($psn->performed_surgery, 60) : 'Sin descripción' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Al vincular, se auto-completa Cirujano y Ayudante desde esa nota (puedes sobreescribir abajo).</small>
            </div>
        </div>

        {{-- Plantilla --}}
        @if($templates->isNotEmpty())
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold">Cargar plantilla (opcional)</label>
                    <div class="d-flex gap-2">
                        <select id="templateSelect" class="form-select">
                            <option value="">— Seleccionar plantilla —</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary" onclick="loadTemplate()">
                            <i class="bi bi-arrow-down-circle"></i> Cargar
                        </button>
                    </div>
                    <small class="text-muted">Solo rellena campos que estén vacíos.</small>
                </div>
            </div>
        @endif

        {{-- TABS --}}
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-valoracion">
                    <i class="bi bi-clipboard-pulse"></i> 1. Valoración Preanestésica
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-registro">
                    <i class="bi bi-activity"></i> 2. Registro Anestésico
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-postanestesica">
                    <i class="bi bi-heart-pulse"></i> 3. Nota Post Anestésica
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- =========================================================== --}}
            {{-- TAB 1: VALORACIÓN PREANESTÉSICA --}}
            {{-- =========================================================== --}}
            <div class="tab-pane fade show active" id="tab-valoracion">

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Datos generales de la cirugía</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tipo de urgencia</label>
                                <select name="surgery_urgency" class="form-select form-select-sm">
                                    <option value="">— No especificado —</option>
                                    <option value="urgencia" {{ old('surgery_urgency', $note->surgery_urgency) === 'urgencia' ? 'selected' : '' }}>Urgencia</option>
                                    <option value="electiva" {{ old('surgery_urgency', $note->surgery_urgency) === 'electiva' ? 'selected' : '' }}>Electiva</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">Diagnóstico prequirúrgico</label>
                                <input type="text" name="preop_diagnosis" class="form-control form-control-sm"
                                       value="{{ old('preop_diagnosis', $note->preop_diagnosis) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Cirugía programada</label>
                                <input type="text" name="planned_surgery" class="form-control form-control-sm"
                                       value="{{ old('planned_surgery', $note->planned_surgery) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Antecedentes --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Antecedentes personales patológicos</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($antecedents as $key => $label)
                                @php
                                    $savedAnt = $note->antecedents[$key] ?? null;
                                    $oldVal   = old("antecedents.{$key}");
                                    if ($oldVal !== null) {
                                        $hasCond = (bool)($oldVal['has_condition'] ?? false);
                                        $evoTime = $oldVal['evolution_time'] ?? '';
                                    } else {
                                        $hasCond = (bool)($savedAnt['has_condition'] ?? false);
                                        $evoTime = $savedAnt['evolution_time'] ?? '';
                                    }
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="row g-1 align-items-center">
                                        <div class="col-5">
                                            <label class="form-label small mb-0">{{ $label }}</label>
                                        </div>
                                        <div class="col-3">
                                            <select name="antecedents[{{ $key }}][has_condition]"
                                                    class="form-select form-select-sm antecedent-toggle"
                                                    data-target="anteced_time_edit_{{ $key }}">
                                                <option value="0" {{ !$hasCond ? 'selected' : '' }}>NO</option>
                                                <option value="1" {{ $hasCond ? 'selected' : '' }}>SI</option>
                                            </select>
                                        </div>
                                        <div class="col-4" id="anteced_time_edit_{{ $key }}"
                                             style="{{ $hasCond ? '' : 'display:none;' }}">
                                            <input type="text"
                                                   name="antecedents[{{ $key }}][evolution_time]"
                                                   class="form-control form-control-sm"
                                                   placeholder="Tiempo"
                                                   value="{{ $evoTime }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Otros antecedentes --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Otros antecedentes</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Medicamentos actuales</label>
                                <textarea name="current_medications" class="form-control form-control-sm" rows="3">{{ old('current_medications', $note->current_medications) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Anestesias previas</label>
                                <textarea name="previous_anesthesias" class="form-control form-control-sm" rows="3">{{ old('previous_anesthesias', $note->previous_anesthesias) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Otros antecedentes</label>
                                <textarea name="other_antecedents" class="form-control form-control-sm" rows="2">{{ old('other_antecedents', $note->other_antecedents) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Padecimiento actual</label>
                                <textarea name="current_illness" id="field_current_illness" class="form-control form-control-sm" rows="4">{{ old('current_illness', $note->current_illness) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Exploración --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Exploración física preanestésica</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Estado de conciencia</label>
                                <select name="consciousness_state" class="form-select form-select-sm">
                                    <option value="">— No evaluado —</option>
                                    <option value="consciente" {{ old('consciousness_state', $note->consciousness_state) === 'consciente' ? 'selected' : '' }}>Consciente</option>
                                    <option value="inconsciente" {{ old('consciousness_state', $note->consciousness_state) === 'inconsciente' ? 'selected' : '' }}>Inconsciente</option>
                                    <option value="desorientado" {{ old('consciousness_state', $note->consciousness_state) === 'desorientado' ? 'selected' : '' }}>Desorientado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Peso (kg)</label>
                                <input type="text" name="weight_kg" class="form-control form-control-sm" value="{{ old('weight_kg', $note->weight_kg) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Talla (m)</label>
                                <input type="text" name="height_m" class="form-control form-control-sm" value="{{ old('height_m', $note->height_m) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">TA</label>
                                <input type="text" name="exam_ta" class="form-control form-control-sm" value="{{ old('exam_ta', $note->exam_ta) }}" placeholder="120/80">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">FC</label>
                                <input type="text" name="exam_fc" class="form-control form-control-sm" value="{{ old('exam_fc', $note->exam_fc) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">FR</label>
                                <input type="text" name="exam_fr" class="form-control form-control-sm" value="{{ old('exam_fr', $note->exam_fr) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Temperatura</label>
                                <input type="text" name="exam_temp" class="form-control form-control-sm" value="{{ old('exam_temp', $note->exam_temp) }}">
                            </div>
                            @foreach([
                                'head_neck_exam' => 'Cabeza y cuello',
                                'airway_exam' => 'Vía aérea',
                                'cardiopulmonary_exam' => 'Cardiopulmonar',
                                'abdomen_exam' => 'Abdomen',
                                'spine_exam' => 'Columna',
                                'extremities_exam' => 'Extremidades',
                                'other_exam' => 'Otros hallazgos',
                            ] as $field => $lbl)
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $lbl }}</label>
                                    <textarea name="{{ $field }}" class="form-control form-control-sm" rows="2">{{ old($field, $note->{$field}) }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Laboratorio --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Laboratorio y gabinete</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach([
                                'lab_hb' => 'Hb', 'lab_hto' => 'Hto', 'lab_tp' => 'TP',
                                'lab_tpt' => 'TPT', 'lab_blood_type_rh' => 'Grupo/Rh',
                                'lab_glucose' => 'Glucosa', 'lab_urea' => 'Urea',
                                'lab_creatinine' => 'Creatinina',
                            ] as $field => $lbl)
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ $lbl }}</label>
                                    <input type="text" name="{{ $field }}" class="form-control form-control-sm" value="{{ old($field, $note->{$field}) }}">
                                </div>
                            @endforeach
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Otros laboratorios</label>
                                <textarea name="other_labs" class="form-control form-control-sm" rows="2">{{ old('other_labs', $note->other_labs) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Estudios de gabinete</label>
                                <textarea name="cabinet_studies" class="form-control form-control-sm" rows="2">{{ old('cabinet_studies', $note->cabinet_studies) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Clasificación ASA y plan anestésico</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">ASA</label>
                                <select name="asa_status" class="form-select form-select-sm">
                                    <option value="">— No clasificado —</option>
                                    @foreach(['I', 'II', 'III', 'IV', 'V'] as $asa)
                                        <option value="{{ $asa }}" {{ old('asa_status', $note->asa_status) === $asa ? 'selected' : '' }}>ASA {{ $asa }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label small fw-semibold">Plan anestésico</label>
                                <textarea name="anesthetic_plan" id="field_anesthetic_plan" class="form-control form-control-sm" rows="3">{{ old('anesthetic_plan', $note->anesthetic_plan) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Indicaciones preanestésicas</label>
                                <textarea name="preanesthetic_indications" class="form-control form-control-sm" rows="3">{{ old('preanesthetic_indications', $note->preanesthetic_indications) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /tab-valoracion --}}

            {{-- =========================================================== --}}
            {{-- TAB 2: REGISTRO ANESTÉSICO --}}
            {{-- =========================================================== --}}
            <div class="tab-pane fade" id="tab-registro">

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Diagnósticos postoperatorios y cirugía realizada</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Diagnóstico postoperatorio</label>
                                <textarea name="postop_diagnosis" class="form-control form-control-sm" rows="3">{{ old('postop_diagnosis', $note->postop_diagnosis) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Cirugía realizada</label>
                                <textarea name="performed_surgery" class="form-control form-control-sm" rows="3">{{ old('performed_surgery', $note->performed_surgery) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Equipo quirúrgico --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Equipo quirúrgico en quirófano</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Cirujano</label>
                                <select name="or_surgeon_user_id" class="form-select form-select-sm"
                                        id="orSurgeonSelect"
                                        data-other-target="orSurgeonOtherWrap"
                                        onchange="toggleOtherField(this)">
                                    <option value="">— No especificado —</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}"
                                            {{ old('or_surgeon_user_id', $orSurgeonIsOther ? 'other' : $note->or_surgeon_user_id) == $doc->id ? 'selected' : '' }}>
                                            Dr(a). {{ $doc->name }} {{ $doc->last_name_one }}
                                        </option>
                                    @endforeach
                                    <option value="other"
                                        {{ old('or_surgeon_user_id', $orSurgeonIsOther ? 'other' : '') === 'other' ? 'selected' : '' }}>
                                        Otro (especificar)
                                    </option>
                                </select>
                                <div id="orSurgeonOtherWrap" class="mt-1"
                                     style="{{ (old('or_surgeon_user_id') === 'other' || $orSurgeonIsOther) ? '' : 'display:none;' }}">
                                    <input type="text" name="or_surgeon_other_name" class="form-control form-control-sm"
                                           placeholder="Nombre completo del cirujano externo"
                                           value="{{ old('or_surgeon_other_name', $note->or_surgeon_other_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Ayudante / Instrumentista</label>
                                <select name="or_assistant_user_id" class="form-select form-select-sm"
                                        id="orAssistantSelect"
                                        data-other-target="orAssistantOtherWrap"
                                        onchange="toggleOtherField(this)">
                                    <option value="">— No especificado —</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}"
                                            {{ old('or_assistant_user_id', $orAssistantIsOther ? 'other' : $note->or_assistant_user_id) == $doc->id ? 'selected' : '' }}>
                                            Dr(a). {{ $doc->name }} {{ $doc->last_name_one }}
                                        </option>
                                    @endforeach
                                    <option value="other"
                                        {{ old('or_assistant_user_id', $orAssistantIsOther ? 'other' : '') === 'other' ? 'selected' : '' }}>
                                        Otro (especificar)
                                    </option>
                                </select>
                                <div id="orAssistantOtherWrap" class="mt-1"
                                     style="{{ (old('or_assistant_user_id') === 'other' || $orAssistantIsOther) ? '' : 'display:none;' }}">
                                    <input type="text" name="or_assistant_other_name" class="form-control form-control-sm"
                                           placeholder="Nombre completo del ayudante externo"
                                           value="{{ old('or_assistant_other_name', $note->or_assistant_other_name) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Intubación --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Intubación y monitoreo</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Hoja de laringoscopio</label>
                                <input type="text" name="intubation_blade" class="form-control form-control-sm" value="{{ old('intubation_blade', $note->intubation_blade) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Cánula</label>
                                <input type="text" name="intubation_cannula" class="form-control form-control-sm" value="{{ old('intubation_cannula', $note->intubation_cannula) }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="intubation_technical_difficulty" value="0">
                                    <input class="form-check-input" type="checkbox" name="intubation_technical_difficulty" value="1"
                                           id="intubDiff" {{ old('intubation_technical_difficulty', $note->intubation_technical_difficulty) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="intubDiff">Dificultad técnica</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Detalle dificultad</label>
                                <input type="text" name="intubation_difficulty_detail" class="form-control form-control-sm" value="{{ old('intubation_difficulty_detail', $note->intubation_difficulty_detail) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Notas de ventilación</label>
                                <textarea name="ventilation_notes" class="form-control form-control-sm" rows="2">{{ old('ventilation_notes', $note->ventilation_notes) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input type="hidden" name="continuous_ecg" value="0">
                                        <input class="form-check-input" type="checkbox" name="continuous_ecg" value="1"
                                               id="ecg" {{ old('continuous_ecg', $note->continuous_ecg) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="ecg">ECG continuo</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="pulse_oximetry" value="0">
                                        <input class="form-check-input" type="checkbox" name="pulse_oximetry" value="1"
                                               id="oximetry" {{ old('pulse_oximetry', $note->pulse_oximetry) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="oximetry">Oximetría</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="capnography" value="0">
                                        <input class="form-check-input" type="checkbox" name="capnography" value="1"
                                               id="capno" {{ old('capnography', $note->capnography) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="capno">Capnografía</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Líquidos --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Balance de líquidos (ml)</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-12"><h6 class="text-muted small mb-1">Ingresos</h6></div>
                            @foreach(['fluids_in_hartmann' => 'Hartmann', 'fluids_in_glucose' => 'Glucosa', 'fluids_in_nacl' => 'NaCl'] as $f => $l)
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ $l }}</label>
                                    <input type="number" name="{{ $f }}" class="form-control form-control-sm" value="{{ old($f, $note->{$f}) }}" min="0">
                                </div>
                            @endforeach
                            <div class="col-12"><h6 class="text-muted small mb-1 mt-2">Egresos</h6></div>
                            @foreach(['fluids_out_diuresis' => 'Diuresis', 'fluids_out_bleeding' => 'Sangrado', 'fluids_out_insensible_losses' => 'Pérd. insensibles'] as $f => $l)
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ $l }}</label>
                                    <input type="number" name="{{ $f }}" class="form-control form-control-sm" value="{{ old($f, $note->{$f}) }}" min="0">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @include('anesthesia-notes._aldrete-scale', [
                    'fieldName'     => 'aldrete_or_exit',
                    'currentValues' => old('aldrete_or_exit', $note->aldrete_or_exit ?? []),
                    'title'         => 'Escala de Aldrete al salir de quirófano',
                ])

                {{-- Anestesia regional --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Anestesia regional</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tipo</label>
                                <input type="text" name="regional_anesthesia_type" class="form-control form-control-sm" value="{{ old('regional_anesthesia_type', $note->regional_anesthesia_type) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Aguja</label>
                                <input type="text" name="regional_needle" class="form-control form-control-sm" value="{{ old('regional_needle', $note->regional_needle) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nivel de punción</label>
                                <input type="text" name="regional_puncture_level" class="form-control form-control-sm" value="{{ old('regional_puncture_level', $note->regional_puncture_level) }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="regional_catheter" value="">
                                    <input class="form-check-input" type="checkbox" name="regional_catheter" value="1"
                                           id="regCatheter" {{ old('regional_catheter', $note->regional_catheter) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="regCatheter">Catéter</label>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label small fw-semibold">Agentes administrados</label>
                                <textarea name="regional_agents_administered" class="form-control form-control-sm" rows="2">{{ old('regional_agents_administered', $note->regional_agents_administered) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tiempos --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Tiempos quirúrgicos</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Inicio anestesia</label>
                                <input type="datetime-local" name="anesthesia_start" class="form-control form-control-sm"
                                       value="{{ old('anesthesia_start', $note->anesthesia_start?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Fin anestesia</label>
                                <input type="datetime-local" name="anesthesia_end" class="form-control form-control-sm"
                                       value="{{ old('anesthesia_end', $note->anesthesia_end?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Inicio cirugía</label>
                                <input type="datetime-local" name="surgery_start" class="form-control form-control-sm"
                                       value="{{ old('surgery_start', $note->surgery_start?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Fin cirugía</label>
                                <input type="datetime-local" name="surgery_end" class="form-control form-control-sm"
                                       value="{{ old('surgery_end', $note->surgery_end?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tiempo anestésico total</label>
                                <input type="text" name="anesthetic_time_total" class="form-control form-control-sm" value="{{ old('anesthetic_time_total', $note->anesthetic_time_total) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Dosis total</label>
                                <input type="text" name="total_dose" class="form-control form-control-sm" value="{{ old('total_dose', $note->total_dose) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Signos vitales transoperatorios --}}
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;">
                        <strong>Signos vitales durante el transoperatorio</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Hora de inicio</label>
                                <input type="time" id="gridStartTime" class="form-control form-control-sm" step="300">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Hora de fin</label>
                                <input type="time" id="gridEndTime" class="form-control form-control-sm" step="300">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-primary" onclick="generateFiveMinuteGrid()">
                                    <i class="bi bi-grid-3x3"></i> Generar cuadrícula cada 5 min
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="vitalReadingsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hora</th><th>TA Sist.</th><th>TA Diast.</th>
                                        <th>FC</th><th>FR</th><th>Temp</th><th>SpO2</th>
                                        <th>Evento</th><th>Hartmann (ml)</th><th>Glucosa (ml)</th><th>NaCl (ml)</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($note->vitalReadings as $i => $reading)
                                        <tr>
                                            <td><input type="time" step="300" name="vital_readings[{{ $i }}][reading_time]" class="form-control form-control-sm" value="{{ \Illuminate\Support\Str::substr($reading->reading_time, 0, 5) }}" readonly></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][ta_sys]" class="form-control form-control-sm" value="{{ $reading->ta_sys }}" style="min-width:60px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][ta_dia]" class="form-control form-control-sm" value="{{ $reading->ta_dia }}" style="min-width:60px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][fc]" class="form-control form-control-sm" value="{{ $reading->fc }}" style="min-width:60px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][fr]" class="form-control form-control-sm" value="{{ $reading->fr }}" style="min-width:55px;"></td>
                                            <td><input type="number" step="0.1" name="vital_readings[{{ $i }}][temp]" class="form-control form-control-sm" value="{{ $reading->temp }}" style="min-width:60px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][spo2]" class="form-control form-control-sm" value="{{ $reading->spo2 }}" style="min-width:55px;"></td>
                                            <td><input type="text" name="vital_readings[{{ $i }}][event_marker]" class="form-control form-control-sm" value="{{ $reading->event_marker }}" placeholder="Evento" style="min-width:100px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][hartmann_ml]" class="form-control form-control-sm" value="{{ $reading->hartmann_ml }}" min="0" placeholder="ml" style="min-width:60px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][glucose_ml]" class="form-control form-control-sm" value="{{ $reading->glucose_ml }}" min="0" placeholder="ml" style="min-width:60px;"></td>
                                            <td><input type="number" name="vital_readings[{{ $i }}][nacl_ml]" class="form-control form-control-sm" value="{{ $reading->nacl_ml }}" min="0" placeholder="ml" style="min-width:60px;"></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addManualVitalReadingRow()">
                            <i class="bi bi-plus-circle"></i> Agregar renglón manual
                        </button>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Revisión de equipo e incidentes</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Revisión de equipo</label>
                                <textarea name="equipment_review" class="form-control form-control-sm" rows="3">{{ old('equipment_review', $note->equipment_review) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Incidentes en quirófano</label>
                                <textarea name="or_incidents" class="form-control form-control-sm" rows="3">{{ old('or_incidents', $note->or_incidents) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /tab-registro --}}

            {{-- =========================================================== --}}
            {{-- TAB 3: NOTA POST ANESTÉSICA --}}
            {{-- =========================================================== --}}
            <div class="tab-pane fade" id="tab-postanestesica">

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Técnica anestésica y manejo</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Técnica anestésica y fármacos</label>
                                <textarea name="anesthetic_technique_and_drugs" id="field_anesthetic_technique_and_drugs"
                                          class="form-control form-control-sm" rows="5">{{ old('anesthetic_technique_and_drugs', $note->anesthetic_technique_and_drugs) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Hemoderivados y líquidos</label>
                                <textarea name="blood_fluids_administered" class="form-control form-control-sm" rows="3">{{ old('blood_fluids_administered', $note->blood_fluids_administered) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Plan de manejo</label>
                                <textarea name="management_plan" class="form-control form-control-sm" rows="3">{{ old('management_plan', $note->management_plan) }}</textarea>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="incidents_or_accidents" value="">
                                    <input class="form-check-input" type="checkbox" name="incidents_or_accidents" value="1"
                                           id="incidentsCheck" {{ old('incidents_or_accidents', $note->incidents_or_accidents) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="incidentsCheck">Hubo incidentes/accidentes</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Ingreso a UCPA</strong></div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            @foreach(['ucpa_admission_ta' => 'TA', 'ucpa_admission_fc' => 'FC', 'ucpa_admission_fr' => 'FR', 'ucpa_admission_spo2' => 'SpO2'] as $f => $l)
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ $l }}</label>
                                    <input type="text" name="{{ $f }}" class="form-control form-control-sm" value="{{ old($f, $note->{$f}) }}">
                                </div>
                            @endforeach
                        </div>
                        @include('anesthesia-notes._aldrete-scale', [
                            'fieldName'     => 'aldrete_ucpa_admission',
                            'currentValues' => old('aldrete_ucpa_admission', $note->aldrete_ucpa_admission ?? []),
                            'title'         => 'Escala de Aldrete — Ingreso UCPA',
                        ])
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Evolución y alta UCPA</strong></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Evolución y alta de UCPA</label>
                            <textarea name="evolution_and_ucpa_discharge" id="field_evolution_and_ucpa_discharge"
                                      class="form-control form-control-sm" rows="4">{{ old('evolution_and_ucpa_discharge', $note->evolution_and_ucpa_discharge) }}</textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            @foreach(['ucpa_discharge_ta' => 'TA alta UCPA', 'ucpa_discharge_fc' => 'FC alta UCPA', 'ucpa_discharge_fr' => 'FR alta UCPA', 'ucpa_discharge_spo2' => 'SpO2 alta UCPA'] as $f => $l)
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ $l }}</label>
                                    <input type="text" name="{{ $f }}" class="form-control form-control-sm" value="{{ old($f, $note->{$f}) }}">
                                </div>
                            @endforeach
                        </div>
                        @include('anesthesia-notes._aldrete-scale', [
                            'fieldName'     => 'aldrete_ucpa_discharge',
                            'currentValues' => old('aldrete_ucpa_discharge', $note->aldrete_ucpa_discharge ?? []),
                            'title'         => 'Escala de Aldrete — Alta UCPA',
                        ])
                        <div>
                            <label class="form-label small fw-semibold">Control de dolor postoperatorio</label>
                            <textarea name="postop_pain_control" id="field_postop_pain_control"
                                      class="form-control form-control-sm" rows="3">{{ old('postop_pain_control', $note->postop_pain_control) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header" style="background-color:#E91E63; color:white;"><strong>Alta de anestesiología</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">TA</label>
                                <input type="text" name="discharge_ta" class="form-control form-control-sm" value="{{ old('discharge_ta', $note->discharge_ta) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Pulso</label>
                                <input type="text" name="discharge_pulse" class="form-control form-control-sm" value="{{ old('discharge_pulse', $note->discharge_pulse) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Respiración</label>
                                <input type="text" name="discharge_resp" class="form-control form-control-sm" value="{{ old('discharge_resp', $note->discharge_resp) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Conciencia</label>
                                <select name="discharge_consciousness" class="form-select form-select-sm">
                                    <option value="">— —</option>
                                    <option value="consciente" {{ old('discharge_consciousness', $note->discharge_consciousness) === 'consciente' ? 'selected' : '' }}>Consciente</option>
                                    <option value="somnoliento" {{ old('discharge_consciousness', $note->discharge_consciousness) === 'somnoliento' ? 'selected' : '' }}>Somnoliento</option>
                                    <option value="inconsciente" {{ old('discharge_consciousness', $note->discharge_consciousness) === 'inconsciente' ? 'selected' : '' }}>Inconsciente</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Diuresis</label>
                                <input type="text" name="discharge_diuresis" class="form-control form-control-sm" value="{{ old('discharge_diuresis', $note->discharge_diuresis) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Dolor</label>
                                <input type="text" name="discharge_pain" class="form-control form-control-sm" value="{{ old('discharge_pain', $note->discharge_pain) }}">
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-3 pt-4">
                                    @foreach([
                                        'discharge_nausea' => ['id' => 'discNausea2', 'label' => 'Náusea'],
                                        'discharge_vomiting' => ['id' => 'discVomiting2', 'label' => 'Vómito'],
                                        'discharge_headache' => ['id' => 'discHeadache2', 'label' => 'Cefálea'],
                                        'discharge_ambulation' => ['id' => 'discAmbulation2', 'label' => 'Deambula'],
                                    ] as $f => $meta)
                                        <div class="form-check">
                                            <input type="hidden" name="{{ $f }}" value="0">
                                            <input class="form-check-input" type="checkbox" name="{{ $f }}" value="1"
                                                   id="{{ $meta['id'] }}" {{ old($f, $note->{$f}) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $meta['id'] }}">{{ $meta['label'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Evolución</label>
                                <textarea name="discharge_evolution" class="form-control form-control-sm" rows="3">{{ old('discharge_evolution', $note->discharge_evolution) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Indicaciones al alta</label>
                                <textarea name="discharge_indications" class="form-control form-control-sm" rows="3">{{ old('discharge_indications', $note->discharge_indications) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /tab-postanestesica --}}

        </div>{{-- /tab-content --}}

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Guardar cambios</button>
            <a href="{{ route('anesthesiaNotes.show', $note) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
function toggleOtherField(selectEl) {
    var targetId = selectEl.dataset.otherTarget;
    var wrapper = document.getElementById(targetId);
    if (wrapper) wrapper.style.display = selectEl.value === 'other' ? '' : 'none';
}

document.querySelectorAll('.antecedent-toggle').forEach(function(select) {
    select.addEventListener('change', function() {
        var target = document.getElementById(this.dataset.target);
        if (target) target.style.display = this.value === '1' ? '' : 'none';
    });
});

let vitalReadingIndex = {{ $note->vitalReadings->count() }};

function generateFiveMinuteGrid() {
    var startVal = document.getElementById('gridStartTime').value;
    var endVal   = document.getElementById('gridEndTime').value;
    if (!startVal || !endVal) { alert('Captura hora de inicio y fin primero.'); return; }
    var startMinutes = parseInt(startVal.split(':')[0]) * 60 + parseInt(startVal.split(':')[1]);
    var endMinutes   = parseInt(endVal.split(':')[0])   * 60 + parseInt(endVal.split(':')[1]);
    if (endMinutes < startMinutes) endMinutes += 24 * 60;
    startMinutes = Math.floor(startMinutes / 5) * 5;
    endMinutes   = Math.ceil(endMinutes / 5) * 5;
    var tbody = document.querySelector('#vitalReadingsTable tbody');
    var existingTimes = Array.from(tbody.querySelectorAll('input[type="time"]')).map(function(i) { return i.value; });
    for (var m = startMinutes; m <= endMinutes; m += 5) {
        var h = Math.floor(m / 60) % 24, min = m % 60;
        var timeStr = String(h).padStart(2,'0') + ':' + String(min).padStart(2,'0');
        if (existingTimes.indexOf(timeStr) !== -1) continue;
        addGridRow(timeStr);
        existingTimes.push(timeStr);
    }
    sortRowsByTime();
}

function addGridRow(timeStr) {
    var tbody = document.querySelector('#vitalReadingsTable tbody');
    var row = document.createElement('tr');
    row.innerHTML = buildRowHtml(vitalReadingIndex++, timeStr, true);
    tbody.appendChild(row);
}

function addManualVitalReadingRow() {
    var tbody = document.querySelector('#vitalReadingsTable tbody');
    var row = document.createElement('tr');
    row.innerHTML = buildRowHtml(vitalReadingIndex++, '', false);
    tbody.appendChild(row);
}

function buildRowHtml(i, timeStr, readonly) {
    var ro = readonly ? ' readonly' : '';
    return '<td><input type="time" step="300" name="vital_readings[' + i + '][reading_time]" class="form-control form-control-sm" value="' + timeStr + '"' + ro + '></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][ta_sys]" class="form-control form-control-sm" style="min-width:60px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][ta_dia]" class="form-control form-control-sm" style="min-width:60px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][fc]" class="form-control form-control-sm" style="min-width:60px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][fr]" class="form-control form-control-sm" style="min-width:55px;"></td>'
         + '<td><input type="number" step="0.1" name="vital_readings[' + i + '][temp]" class="form-control form-control-sm" style="min-width:60px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][spo2]" class="form-control form-control-sm" style="min-width:55px;"></td>'
         + '<td><input type="text" name="vital_readings[' + i + '][event_marker]" class="form-control form-control-sm" placeholder="Evento" style="min-width:100px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][hartmann_ml]" class="form-control form-control-sm" min="0" placeholder="ml" style="min-width:60px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][glucose_ml]" class="form-control form-control-sm" min="0" placeholder="ml" style="min-width:60px;"></td>'
         + '<td><input type="number" name="vital_readings[' + i + '][nacl_ml]" class="form-control form-control-sm" min="0" placeholder="ml" style="min-width:60px;"></td>'
         + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()"><i class="bi bi-trash"></i></button></td>';
}

function sortRowsByTime() {
    var tbody = document.querySelector('#vitalReadingsTable tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort(function(a, b) {
        var tA = a.querySelector('input[type="time"]').value || '99:99';
        var tB = b.querySelector('input[type="time"]').value || '99:99';
        return tA.localeCompare(tB);
    });
    rows.forEach(function(r) { tbody.appendChild(r); });
}

function loadTemplate() {
    var select = document.getElementById('templateSelect');
    if (!select || !select.value) return;
    fetch('{{ route("anesthesiaNoteTemplates.content", ":id") }}'.replace(':id', select.value))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var sects = data.sections || {};
            var mapping = {
                'current_illness': 'field_current_illness',
                'anesthetic_plan': 'field_anesthetic_plan',
                'anesthetic_technique_and_drugs': 'field_anesthetic_technique_and_drugs',
                'evolution_and_ucpa_discharge': 'field_evolution_and_ucpa_discharge',
                'postop_pain_control': 'field_postop_pain_control',
            };
            Object.keys(mapping).forEach(function(key) {
                if (sects[key]) {
                    var el = document.getElementById(mapping[key]);
                    if (el && !el.value.trim()) el.value = sects[key];
                }
            });
        })
        .catch(function() { alert('No se pudo cargar la plantilla.'); });
}
</script>
@endsection
