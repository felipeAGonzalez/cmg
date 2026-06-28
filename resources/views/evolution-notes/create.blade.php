@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:900px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('evolutionNotes.index', $stay) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h2 class="mb-0">Nueva Nota de Evolución</h2>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Datos del paciente (solo lectura) --}}
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

    {{-- Signos vitales (solo display) --}}
    <div class="card mb-3 border-0 shadow-sm" style="background:#f8f9fa;">
        <div class="card-body small">
            <h6 class="mb-2"><i class="bi bi-heart-pulse me-1" style="color:#E91E63;"></i>Signos vitales (últimos registrados)</h6>
            @if($latestVitals)
                <div class="row g-2">
                    <div class="col-auto"><strong>FC:</strong> {{ $latestVitals->heart_rate ?? '—' }}</div>
                    <div class="col-auto"><strong>TA:</strong>
                        {{ $latestVitals->blood_pressure_systolic ?? '—' }}/{{ $latestVitals->blood_pressure_diastolic ?? '—' }}
                    </div>
                    <div class="col-auto"><strong>Temp:</strong> {{ $latestVitals->temperature ?? '—' }}°C</div>
                    <div class="col-auto"><strong>FR:</strong> {{ $latestVitals->respiratory_rate ?? '—' }}</div>
                    <div class="col-auto text-muted">
                        <i class="bi bi-clock me-1"></i>{{ $latestVitals->recorded_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">Sin signos vitales registrados aún.</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('evolutionNotes.store', $stay) }}">
        @csrf

        {{-- Fecha/hora + Médico --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Fecha y hora de la nota <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="note_datetime"
                               value="{{ old('note_datetime', now()->format('Y-m-d\TH:i')) }}"
                               class="form-control @error('note_datetime') is-invalid @enderror" required>
                        @error('note_datetime')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @if(!auth()->user()->isDoctor())
                    <div class="col-md-5">
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
                    @endif
                </div>
            </div>
        </div>

        {{-- Rango de medicamentos --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-capsule me-1"></i>Medicamentos administrados a incluir en el PDF</h6>
                <div class="btn-group btn-group-sm mb-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="setMedRange('today')">Hoy</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setMedRange('24h')">Últimas 24h</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setMedRange('all')">Toda la estancia</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setMedRange('none')">Sin medicamentos</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Desde</label>
                        <input type="datetime-local" name="medications_from" id="medications_from"
                               value="{{ old('medications_from', now()->startOfDay()->format('Y-m-d\TH:i')) }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Hasta</label>
                        <input type="datetime-local" name="medications_to" id="medications_to"
                               value="{{ old('medications_to', now()->endOfDay()->format('Y-m-d\TH:i')) }}"
                               class="form-control form-control-sm">
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

        {{-- Secciones SOAP --}}
        @foreach($sections as $key => $config)
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold">{{ $config['label'] }}</label>
                    <textarea name="{{ $key }}" rows="4"
                              placeholder="{{ $config['placeholder'] }}"
                              class="form-control @error($key) is-invalid @enderror"
                    >{{ old($key) }}</textarea>
                    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('evolutionNotes.index', $stay) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Crear nota
            </button>
        </div>
    </form>
</div>

<script>
const admissionDate = '{{ $stay->admission_date->format("Y-m-d\TH:i") }}';

function setMedRange(range) {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const toLocal = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;

    let from, to;

    if (range === 'today') {
        from = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0);
        to   = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59);
    } else if (range === '24h') {
        from = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        to   = now;
    } else if (range === 'all') {
        from = new Date(admissionDate);
        to   = now;
    } else {
        document.getElementById('medications_from').value = '';
        document.getElementById('medications_to').value = '';
        return;
    }

    document.getElementById('medications_from').value = toLocal(from);
    document.getElementById('medications_to').value   = toLocal(to);
}

function loadTemplate() {
    const select = document.getElementById('template_select');
    const templateId = select.value;
    if (!templateId) { alert('Selecciona una plantilla primero.'); return; }

    fetch(`/evolution-templates/${templateId}/content`)
        .then(r => r.json())
        .then(data => {
            const fields = ['antecedents','subjective','objective','analysis','diagnosis','prognosis','plan'];
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
