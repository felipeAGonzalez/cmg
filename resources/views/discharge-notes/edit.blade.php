@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('stays.show', $stay) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0">
                <i class="bi bi-box-arrow-right me-2" style="color:#E91E63;"></i>Nota de Alta
            </h2>
            <p class="text-muted mb-0 small">
                Paciente: <strong>{{ $patient->fullName() }}</strong>
                @if($stay->discharge_date !== null)
                    &middot;
                    <span class="badge bg-secondary">
                        Alta el {{ $stay->discharge_date->format('d/m/Y H:i') }}
                    </span>
                @endif
            </p>
        </div>
        @if($note->exists)
            <form method="POST" action="{{ route('dischargeNote.destroy', $stay) }}"
                  onsubmit="return confirm('¿Eliminar la Nota de Alta? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
        @endif
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Datos del paciente --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-light fw-semibold">Datos del paciente</div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6"><strong>Nombre:</strong> {{ $patient->fullName() }}</div>
                <div class="col-md-3">
                    <strong>Edad:</strong>
                    {{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age . ' años' : '—' }}
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong>
                    {{ $patient->gender === 'M' ? 'Masculino' : ($patient->gender === 'F' ? 'Femenino' : '—') }}
                </div>
                <div class="col-md-3">
                    <strong>F. Ingreso:</strong> {{ $stay->admission_date->format('d/m/Y') }}
                </div>
                <div class="col-md-3">
                    <strong>F. Egreso:</strong>
                    {{ $stay->discharge_date ? $stay->discharge_date->format('d/m/Y') : 'Aún hospitalizado' }}
                </div>
                <div class="col-md-3"><strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}</div>
                <div class="col-md-3"><strong>Expediente:</strong> {{ $patient->id }}</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('dischargeNote.update', $stay) }}">
        @csrf
        @method('PUT')

        {{-- Médico tratante (solo si no es doctor) --}}
        @if(!auth()->user()->isDoctor())
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold">
                        Médico tratante <span class="text-danger">*</span>
                    </label>
                    <select name="attending_doctor_id"
                            class="form-select @error('attending_doctor_id') is-invalid @enderror" required>
                        <option value="">Seleccionar...</option>
                        @foreach($availableDoctors as $doc)
                            <option value="{{ $doc->id }}"
                                    {{ old('attending_doctor_id', $note->attending_doctor_id) == $doc->id ? 'selected' : '' }}>
                                Dr(a). {{ $doc->fullName() }}
                                @if(method_exists($doc, 'specialtiesLabel') && $doc->specialtiesLabel())
                                    — {{ $doc->specialtiesLabel() }}
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

        {{-- Selector de plantilla --}}
        @if($templates->isNotEmpty())
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-2">
                        <i class="bi bi-file-text me-1"></i>Cargar plantilla (opcional)
                    </h6>
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

        {{-- 6 secciones narrativas --}}
        @foreach($sections as $key => $config)
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold">{{ $config['label'] }}</label>
                    <textarea name="{{ $key }}" rows="5"
                              placeholder="{{ $config['placeholder'] }}"
                              class="form-control @error($key) is-invalid @enderror"
                    >{{ old($key, $note->{$key}) }}</textarea>
                    @error($key)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('stays.show', $stay) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Guardar Nota de Alta
            </button>
        </div>
    </form>
</div>

<script>
function loadTemplate() {
    const select = document.getElementById('template_select');
    const templateId = select.value;
    if (!templateId) { alert('Selecciona una plantilla primero.'); return; }

    fetch(`/discharge-templates/${templateId}/content`)
        .then(r => r.json())
        .then(data => {
            const fields = ['admission_diagnosis', 'discharge_diagnosis', 'clinical_summary',
                           'physical_examination_at_discharge', 'plan_and_treatment_at_discharge', 'prognosis'];
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
