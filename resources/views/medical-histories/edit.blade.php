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
(function() {
    const selector = document.getElementById('template-selector');
    const loadBtn = document.getElementById('load-template-btn');
    const statusEl = document.getElementById('template-load-status');

    if (!selector || !loadBtn) return;

    selector.addEventListener('change', function() {
        loadBtn.disabled = !this.value;
    });

    loadBtn.addEventListener('click', function() {
        const templateId = selector.value;
        if (!templateId) return;

        statusEl.textContent = 'Cargando plantilla...';
        statusEl.className = 'small text-muted mt-2';
        loadBtn.disabled = true;

        fetch('/medical-templates/' + templateId + '/content', {
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
