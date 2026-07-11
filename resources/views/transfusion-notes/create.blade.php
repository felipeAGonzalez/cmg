@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('transfusionNotes.index', $stay) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h2 class="mb-0">Nueva Nota Transfusional</h2>
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

    <form method="POST" action="{{ route('transfusionNotes.store', $stay) }}">
        @csrf

        {{-- Médico (si no es doctor) --}}
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

        {{-- Vinculación a Lista de Verificación --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">Vincular a Lista de Verificación de Transfusión (opcional)</label>
                <select name="transfusion_checklist_id" class="form-select">
                    <option value="">— Sin vincular —</option>
                    @foreach($availableChecklists as $checklist)
                        <option value="{{ $checklist->id }}"
                                {{ old('transfusion_checklist_id') == $checklist->id ? 'selected' : '' }}>
                            Folio {{ $checklist->folio ?? $checklist->id }}
                            — Finalizada {{ $checklist->finalized_at->format('d/m/Y H:i') }}
                        </option>
                    @endforeach
                </select>
                @if($availableChecklists->isEmpty())
                    <small class="text-muted d-block mt-1">
                        No hay Listas de Verificación finalizadas en esta estancia aún.
                    </small>
                @endif
            </div>
        </div>

        {{-- Fechas de inicio / término --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-clock me-1"></i>Tiempos de la transfusión</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Fecha y hora de inicio</label>
                        <input type="datetime-local" name="start_datetime" class="form-control @error('start_datetime') is-invalid @enderror"
                               value="{{ old('start_datetime') }}">
                        @error('start_datetime')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha y hora de término</label>
                        <input type="datetime-local" name="end_datetime" class="form-control @error('end_datetime') is-invalid @enderror"
                               value="{{ old('end_datetime') }}">
                        @error('end_datetime')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <select id="template_select" class="form-select form-select-sm" style="max-width:300px;">
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

        {{-- Sección: Diagnósticos e indicación --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['diagnoses_and_indication']['label'] }}</label>
                <textarea name="diagnoses_and_indication" rows="4"
                          placeholder="{{ $sections['diagnoses_and_indication']['placeholder'] }}"
                          class="form-control @error('diagnoses_and_indication') is-invalid @enderror"
                >{{ old('diagnoses_and_indication') }}</textarea>
                @error('diagnoses_and_indication')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Sección: Verificación de compatibilidad --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['compatibility_verification']['label'] }}</label>
                <textarea name="compatibility_verification" rows="4"
                          placeholder="{{ $sections['compatibility_verification']['placeholder'] }}"
                          class="form-control @error('compatibility_verification') is-invalid @enderror"
                >{{ old('compatibility_verification') }}</textarea>
                @error('compatibility_verification')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Signos vitales PREVIOS --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">Signos vitales previos a la transfusión</label>
                <div class="row g-2">
                    <div class="col">
                        <input type="text" name="pre_ta" placeholder="TA (mmHg)"
                               class="form-control form-control-sm"
                               value="{{ old('pre_ta') }}">
                        <small class="text-muted">TA</small>
                    </div>
                    <div class="col">
                        <input type="text" name="pre_fc" placeholder="lpm"
                               class="form-control form-control-sm"
                               value="{{ old('pre_fc') }}">
                        <small class="text-muted">FC</small>
                    </div>
                    <div class="col">
                        <input type="text" name="pre_fr" placeholder="rpm"
                               class="form-control form-control-sm"
                               value="{{ old('pre_fr') }}">
                        <small class="text-muted">FR</small>
                    </div>
                    <div class="col">
                        <input type="text" name="pre_temp" placeholder="°C"
                               class="form-control form-control-sm"
                               value="{{ old('pre_temp') }}">
                        <small class="text-muted">TEMP</small>
                    </div>
                    <div class="col">
                        <input type="text" name="pre_spo2" placeholder="%"
                               class="form-control form-control-sm"
                               value="{{ old('pre_spo2') }}">
                        <small class="text-muted">SpO2</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Evolución durante y posterior --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['evolution_narrative']['label'] }}</label>
                <textarea name="evolution_narrative" rows="5"
                          placeholder="{{ $sections['evolution_narrative']['placeholder'] }}"
                          class="form-control @error('evolution_narrative') is-invalid @enderror"
                >{{ old('evolution_narrative') }}</textarea>
                @error('evolution_narrative')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Signos vitales POSTERIORES --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">Signos vitales posteriores a la transfusión</label>
                <div class="row g-2">
                    <div class="col">
                        <input type="text" name="post_ta" placeholder="TA (mmHg)"
                               class="form-control form-control-sm"
                               value="{{ old('post_ta') }}">
                        <small class="text-muted">TA</small>
                    </div>
                    <div class="col">
                        <input type="text" name="post_fc" placeholder="lpm"
                               class="form-control form-control-sm"
                               value="{{ old('post_fc') }}">
                        <small class="text-muted">FC</small>
                    </div>
                    <div class="col">
                        <input type="text" name="post_fr" placeholder="rpm"
                               class="form-control form-control-sm"
                               value="{{ old('post_fr') }}">
                        <small class="text-muted">FR</small>
                    </div>
                    <div class="col">
                        <input type="text" name="post_temp" placeholder="°C"
                               class="form-control form-control-sm"
                               value="{{ old('post_temp') }}">
                        <small class="text-muted">TEMP</small>
                    </div>
                    <div class="col">
                        <input type="text" name="post_spo2" placeholder="%"
                               class="form-control form-control-sm"
                               value="{{ old('post_spo2') }}">
                        <small class="text-muted">SpO2</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Conclusión --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <label class="form-label fw-semibold">{{ $sections['conclusion']['label'] }}</label>
                <textarea name="conclusion" rows="3"
                          placeholder="{{ $sections['conclusion']['placeholder'] }}"
                          class="form-control @error('conclusion') is-invalid @enderror"
                >{{ old('conclusion') }}</textarea>
                @error('conclusion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('transfusionNotes.index', $stay) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Crear nota
            </button>
        </div>
    </form>
</div>

<script>
function loadTemplate() {
    const select = document.getElementById('template_select');
    const templateId = select.value;
    if (!templateId) { alert('Selecciona una plantilla primero.'); return; }

    fetch(`/transfusion-note-templates/${templateId}/content`)
        .then(r => r.json())
        .then(data => {
            const fields = ['diagnoses_and_indication', 'compatibility_verification', 'evolution_narrative', 'conclusion'];
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
