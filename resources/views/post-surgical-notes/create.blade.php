@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('postSurgicalNotes.index', $stay) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h2 class="mb-0">Nueva Nota Postquirúrgica</h2>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Datos del paciente --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-light fw-semibold">Datos del paciente</div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6"><strong>Nombre:</strong> {{ $patient->fullName() }}</div>
                <div class="col-md-3"><strong>Sexo:</strong> {{ $patient->gender === 'M' ? 'Masculino' : 'Femenino' }}</div>
                <div class="col-md-3">
                    <strong>Edad:</strong>
                    {{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age . ' años' : '—' }}
                </div>
                <div class="col-md-3"><strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}</div>
                <div class="col-md-3"><strong>Expediente:</strong> {{ $patient->id }}</div>
                <div class="col-md-3">
                    <strong>F. Ingreso:</strong> {{ $stay->admission_date->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('postSurgicalNotes.store', $stay) }}">
        @csrf

        {{-- Médico tratante (si no es doctor) --}}
        @if(!auth()->user()->isDoctor())
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">Médico tratante <span class="text-danger">*</span></label>
                <select name="attending_doctor_id" class="form-select @error('attending_doctor_id') is-invalid @enderror" required>
                    <option value="">Seleccionar...</option>
                    @foreach($availableDoctors as $doc)
                        <option value="{{ $doc->id }}"
                                {{ old('attending_doctor_id') == $doc->id ? 'selected' : '' }}>
                            Dr(a). {{ $doc->fullName() }}
                        </option>
                    @endforeach
                </select>
                @error('attending_doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        @endif

        {{-- Datos de la cirugía --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-calendar-event me-1"></i>Datos de la cirugía</h6>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Fecha de cirugía</label>
                        <input type="date" name="surgery_date" class="form-control @error('surgery_date') is-invalid @enderror"
                               value="{{ old('surgery_date') }}">
                        @error('surgery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hora</label>
                        <input type="time" name="surgery_time" class="form-control @error('surgery_time') is-invalid @enderror"
                               value="{{ old('surgery_time') }}">
                        @error('surgery_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo de cirugía</label>
                        <select name="surgery_type" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="urgencia" {{ old('surgery_type') === 'urgencia' ? 'selected' : '' }}>Urgencia</option>
                            <option value="programada" {{ old('surgery_type') === 'programada' ? 'selected' : '' }}>Programada</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Tiempo quirúrgico</label>
                        <input type="text" name="surgical_time" placeholder="Ej. 2:30 hrs"
                               class="form-control @error('surgical_time') is-invalid @enderror"
                               value="{{ old('surgical_time') }}">
                        @error('surgical_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tiempo de isquemia <span class="text-muted small">(opcional)</span></label>
                        <input type="text" name="ischemia_time" placeholder="Ej. 45 min"
                               class="form-control @error('ischemia_time') is-invalid @enderror"
                               value="{{ old('ischemia_time') }}">
                        @error('ischemia_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Selector de plantilla --}}
        @if($templates->isNotEmpty())
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-file-text me-1"></i>Cargar plantilla (opcional)</h6>
                <div class="d-flex gap-2 align-items-center mb-1">
                    <select id="template_select" class="form-select form-select-sm" style="max-width:320px;">
                        <option value="">— Sin plantilla —</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadTemplate()">
                        <i class="bi bi-arrow-down-circle"></i> Cargar
                    </button>
                </div>
                <small class="text-muted">Solo prellena campos vacíos, no sobrescribe lo que ya escribiste.</small>
            </div>
        </div>
        @endif

        {{-- Diagnóstico prequirúrgico --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['preop_diagnosis']['label'] }}</label>
                <textarea name="preop_diagnosis" rows="3"
                          placeholder="{{ $sections['preop_diagnosis']['placeholder'] }}"
                          class="form-control @error('preop_diagnosis') is-invalid @enderror"
                >{{ old('preop_diagnosis') }}</textarea>
                @error('preop_diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Diagnóstico postquirúrgico --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['postop_diagnosis']['label'] }}</label>
                <textarea name="postop_diagnosis" rows="3"
                          placeholder="{{ $sections['postop_diagnosis']['placeholder'] }}"
                          class="form-control @error('postop_diagnosis') is-invalid @enderror"
                >{{ old('postop_diagnosis') }}</textarea>
                @error('postop_diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Cirugía proyectada / realizada --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ $sections['planned_surgery']['label'] }}</label>
                        <textarea name="planned_surgery" rows="3"
                                  placeholder="{{ $sections['planned_surgery']['placeholder'] }}"
                                  class="form-control @error('planned_surgery') is-invalid @enderror"
                        >{{ old('planned_surgery') }}</textarea>
                        @error('planned_surgery')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ $sections['performed_surgery']['label'] }}</label>
                        <textarea name="performed_surgery" rows="3"
                                  placeholder="{{ $sections['performed_surgery']['placeholder'] }}"
                                  class="form-control @error('performed_surgery') is-invalid @enderror"
                        >{{ old('performed_surgery') }}</textarea>
                        @error('performed_surgery')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Complicaciones + Sangrado --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ $sections['complications']['label'] }}</label>
                        <textarea name="complications" rows="3"
                                  placeholder="{{ $sections['complications']['placeholder'] }}"
                                  class="form-control @error('complications') is-invalid @enderror"
                        >{{ old('complications') }}</textarea>
                        @error('complications')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ $sections['bleeding']['label'] }}</label>
                        <textarea name="bleeding" rows="3"
                                  placeholder="{{ $sections['bleeding']['placeholder'] }}"
                                  class="form-control @error('bleeding') is-invalid @enderror"
                        >{{ old('bleeding') }}</textarea>
                        @error('bleeding')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Recuento de textiles --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">Recuento de textiles</label>
                <div class="row g-2">
                    <div class="col-md-4">
                        <select name="textile_count" class="form-select" id="textileCountSelect"
                                onchange="toggleTextileDetail()">
                            <option value="">Seleccionar...</option>
                            <option value="completo" {{ old('textile_count') === 'completo' ? 'selected' : '' }}>Completo</option>
                            <option value="incompleto" {{ old('textile_count') === 'incompleto' ? 'selected' : '' }}>Incompleto</option>
                        </select>
                    </div>
                    <div class="col-md-8" id="textileDetailWrapper"
                         style="{{ old('textile_count') === 'incompleto' ? '' : 'display:none;' }}">
                        <input type="text" name="textile_count_detail" class="form-control"
                               placeholder="Especificar detalle del recuento incompleto"
                               value="{{ old('textile_count_detail') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado del paciente al salir + Pronóstico --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['patient_status_at_exit']['label'] }}</label>
                <textarea name="patient_status_at_exit" rows="3"
                          placeholder="{{ $sections['patient_status_at_exit']['placeholder'] }}"
                          class="form-control mb-3 @error('patient_status_at_exit') is-invalid @enderror"
                >{{ old('patient_status_at_exit') }}</textarea>
                @error('patient_status_at_exit')<div class="invalid-feedback">{{ $message }}</div>@enderror

                <label class="form-label fw-semibold">{{ $sections['prognosis']['label'] }}</label>
                <textarea name="prognosis" rows="2"
                          placeholder="{{ $sections['prognosis']['placeholder'] }}"
                          class="form-control @error('prognosis') is-invalid @enderror"
                >{{ old('prognosis') }}</textarea>
                @error('prognosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Equipo quirúrgico --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header" style="background:#fce4ec; color:#c2185b; font-weight:600;">
                <i class="bi bi-people-fill me-1"></i>Equipo quirúrgico
            </div>
            <div class="card-body">

                {{-- Cirujano --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Cirujano</label>
                        <select name="surgeon_user_id" class="form-select"
                                data-other-target="surgeon_other_name_wrapper"
                                onchange="toggleOtherField(this)">
                            <option value="">— No especificado —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}"
                                        {{ old('surgeon_user_id') == $doc->id ? 'selected' : '' }}>
                                    Dr(a). {{ $doc->name }} {{ $doc->last_name_one }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('surgeon_user_id') === 'other' ? 'selected' : '' }}>
                                Otro (especificar)
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6" id="surgeon_other_name_wrapper"
                         style="{{ old('surgeon_user_id') === 'other' ? '' : 'display:none;' }}">
                        <label class="form-label small">Nombre (si no es usuario del sistema)</label>
                        <input type="text" name="surgeon_other_name" class="form-control"
                               value="{{ old('surgeon_other_name') }}"
                               placeholder="Nombre completo">
                    </div>
                </div>

                {{-- Ayudante/Instrumentista --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ayudante/Instrumentista</label>
                        <select name="assistant_user_id" class="form-select"
                                data-other-target="assistant_other_name_wrapper"
                                onchange="toggleOtherField(this)">
                            <option value="">— No especificado —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}"
                                        {{ old('assistant_user_id') == $doc->id ? 'selected' : '' }}>
                                    Dr(a). {{ $doc->name }} {{ $doc->last_name_one }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('assistant_user_id') === 'other' ? 'selected' : '' }}>
                                Otro (especificar)
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6" id="assistant_other_name_wrapper"
                         style="{{ old('assistant_user_id') === 'other' ? '' : 'display:none;' }}">
                        <label class="form-label small">Nombre (si no es usuario del sistema)</label>
                        <input type="text" name="assistant_other_name" class="form-control"
                               value="{{ old('assistant_other_name') }}"
                               placeholder="Nombre completo">
                    </div>
                </div>

                {{-- Anestesiólogo --}}
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Anestesiólogo</label>
                        <select name="anesthesiologist_user_id" class="form-select"
                                data-other-target="anesthesiologist_other_name_wrapper"
                                onchange="toggleOtherField(this)">
                            <option value="">— No especificado —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}"
                                        {{ old('anesthesiologist_user_id') == $doc->id ? 'selected' : '' }}>
                                    Dr(a). {{ $doc->name }} {{ $doc->last_name_one }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('anesthesiologist_user_id') === 'other' ? 'selected' : '' }}>
                                Otro (especificar)
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6" id="anesthesiologist_other_name_wrapper"
                         style="{{ old('anesthesiologist_user_id') === 'other' ? '' : 'display:none;' }}">
                        <label class="form-label small">Nombre (si no es usuario del sistema)</label>
                        <input type="text" name="anesthesiologist_other_name" class="form-control"
                               value="{{ old('anesthesiologist_other_name') }}"
                               placeholder="Nombre completo">
                    </div>
                </div>
            </div>
        </div>

        {{-- Técnica quirúrgica --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['surgical_technique']['label'] }}</label>
                <textarea name="surgical_technique" rows="8"
                          placeholder="{{ $sections['surgical_technique']['placeholder'] }}"
                          class="form-control @error('surgical_technique') is-invalid @enderror"
                >{{ old('surgical_technique') }}</textarea>
                @error('surgical_technique')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('postSurgicalNotes.index', $stay) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Crear nota
            </button>
        </div>
    </form>
</div>

<script>
function toggleOtherField(selectEl) {
    const targetId = selectEl.dataset.otherTarget;
    const wrapper = document.getElementById(targetId);
    if (!wrapper) return;
    if (selectEl.value === 'other') {
        wrapper.style.display = '';
    } else {
        wrapper.style.display = 'none';
        const input = wrapper.querySelector('input');
        if (input) input.value = '';
    }
}

function toggleTextileDetail() {
    const val = document.getElementById('textileCountSelect').value;
    document.getElementById('textileDetailWrapper').style.display = val === 'incompleto' ? '' : 'none';
}

function loadTemplate() {
    const select = document.getElementById('template_select');
    const templateId = select.value;
    if (!templateId) { alert('Selecciona una plantilla primero.'); return; }

    fetch(`/post-surgical-note-templates/${templateId}/content`)
        .then(r => r.json())
        .then(data => {
            const fields = [
                'preop_diagnosis', 'postop_diagnosis', 'planned_surgery', 'performed_surgery',
                'complications', 'bleeding', 'patient_status_at_exit', 'prognosis', 'surgical_technique'
            ];
            let filled = 0;
            fields.forEach(f => {
                const el = document.querySelector(`textarea[name="${f}"]`);
                if (el && !el.value.trim() && data.sections && data.sections[f]) {
                    el.value = data.sections[f];
                    filled++;
                }
            });
            alert(filled ? `${filled} campo(s) prellenado(s).` : 'Todos los campos ya tienen contenido.');
        })
        .catch(() => alert('Error al cargar la plantilla.'));
}
</script>
@endsection
