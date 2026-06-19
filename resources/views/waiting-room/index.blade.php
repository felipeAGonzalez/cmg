@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-clipboard-pulse"></i> Sala de Espera
                <span class="text-muted fs-5">
                    ({{ $triages->count() }}
                    {{ $triages->count() === 1 ? 'paciente' : 'pacientes' }})
                </span>
            </h2>
            <p class="text-muted mb-0">
                Pacientes con triage pendiente de decisi&oacute;n final.
            </p>
        </div>
        <a href="{{ route('triage.start') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle"></i> Iniciar triage
        </a>
    </div>

    @if($triages->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-check-circle" style="font-size:3rem;"></i>
            <p class="mt-3 fs-5">No hay pacientes en sala de espera.</p>
            <p>
                <a href="{{ route('triage.start') }}" class="btn btn-primary">
                    Iniciar triage para un paciente
                </a>
            </p>
        </div>
    @else
        <div class="row g-3">
            @foreach($triages as $triage)
                @php
                    $colorBorderClass = match($triage->color) {
                        'red' => 'border-danger',
                        'orange' => 'border-warning',
                        'yellow' => 'border-warning',
                        'green' => 'border-success',
                        'blue' => 'border-primary',
                        default => 'border-secondary',
                    };
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-3 {{ $colorBorderClass }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge {{ $triage->colorBadgeClass() }} fs-6">
                                    {{ $triage->colorLabel() }}
                                </span>
                                <span class="text-muted small"
                                      data-waiting-time="{{ $triage->evaluation_started_at->toIso8601String() }}">
                                </span>
                            </div>

                            <h5 class="card-title mb-1">
                                {{ $triage->patient->fullName() }}
                            </h5>
                            <p class="text-muted small mb-2">
                                @if($triage->patient->birth_date)
                                    {{ $triage->patient->birth_date->age }} a&ntilde;os &middot;
                                @endif
                                {{ $triage->patient->gender === 'M' ? 'Masculino' : ($triage->patient->gender === 'F' ? 'Femenino' : '') }}
                            </p>

                            <div class="row g-2 small mb-3">
                                <div class="col-6">
                                    <strong>Decisi&oacute;n:</strong><br>
                                    {{ $triage->decisionLabel() }}
                                </div>
                                <div class="col-6">
                                    <strong>Sitio:</strong><br>
                                    {{ $triage->siteLabel() }}
                                </div>
                                <div class="col-6">
                                    <strong>Puntaje:</strong><br>
                                    {{ $triage->total_score }}
                                </div>
                                <div class="col-6">
                                    <strong>Hora inicio:</strong><br>
                                    {{ $triage->evaluation_started_at->format('H:i') }}
                                </div>
                            </div>

                            @if($triage->hasImmediateAlert())
                                <div class="alert alert-danger py-1 px-2 small mb-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Atenci&oacute;n inmediata requerida
                                </div>
                            @endif

                            <div class="d-flex gap-1">
                                <a href="{{ route('triage.show', $triage) }}"
                                   class="btn btn-sm btn-outline-secondary flex-grow-1">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-primary flex-grow-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#dispositionModal-{{ $triage->id }}">
                                    <i class="bi bi-arrow-right-circle"></i> Decisi&oacute;n
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal de disposición --}}
                <div class="modal fade" id="dispositionModal-{{ $triage->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Disposici&oacute;n &mdash; {{ $triage->patient->fullName() }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">
                                    Clasificaci&oacute;n:
                                    <span class="badge {{ $triage->colorBadgeClass() }}">
                                        {{ $triage->colorLabel() }} &mdash; {{ $triage->decisionLabel() }}
                                    </span>
                                    <br>
                                    Sugerencia autom&aacute;tica:
                                    <strong>{{ $triage->suggestedDestinationLabel() }}</strong>
                                </p>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Disposici&oacute;n final
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-grid gap-2">
                                        <label class="border rounded p-2 d-flex align-items-center disposition-option" style="cursor:pointer;" data-modal-id="{{ $triage->id }}">
                                            <input type="radio" name="disposition_{{ $triage->id }}"
                                                   value="hospitalized"
                                                   class="form-check-input me-2 disposition-radio" required>
                                            <div>
                                                <strong>Hospitalizar</strong>
                                                <div class="text-muted small">
                                                    Crear estancia y asignar cuarto
                                                </div>
                                            </div>
                                        </label>
                                        <label class="border rounded p-2 d-flex align-items-center disposition-option" style="cursor:pointer;" data-modal-id="{{ $triage->id }}">
                                            <input type="radio" name="disposition_{{ $triage->id }}"
                                                   value="ambulatory"
                                                   class="form-check-input me-2 disposition-radio">
                                            <div>
                                                <strong>Atenci&oacute;n ambulatoria</strong>
                                                <div class="text-muted small">
                                                    Se atiende y se va a casa
                                                </div>
                                            </div>
                                        </label>
                                        <label class="border rounded p-2 d-flex align-items-center disposition-option" style="cursor:pointer;" data-modal-id="{{ $triage->id }}">
                                            <input type="radio" name="disposition_{{ $triage->id }}"
                                                   value="refused"
                                                   class="form-check-input me-2 disposition-radio">
                                            <div>
                                                <strong>Rechaz&oacute; atenci&oacute;n</strong>
                                                <div class="text-muted small">
                                                    El paciente decidi&oacute; no quedarse
                                                </div>
                                            </div>
                                        </label>
                                        <label class="border rounded p-2 d-flex align-items-center disposition-option" style="cursor:pointer;" data-modal-id="{{ $triage->id }}">
                                            <input type="radio" name="disposition_{{ $triage->id }}"
                                                   value="referred"
                                                   class="form-check-input me-2 disposition-radio">
                                            <div>
                                                <strong>Referido</strong>
                                                <div class="text-muted small">
                                                    Enviado a otra instituci&oacute;n
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Campos extra para hospitalización --}}
                                <div id="hospitalize-fields-{{ $triage->id }}" class="border-top pt-3" style="display:none;">
                                    <h6 class="mb-3">Datos para crear la estancia</h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Cuarto <span class="text-danger">*</span></label>
                                            <select name="room_id" class="form-select" form="hospitalize-form-{{ $triage->id }}">
                                                <option value="">Selecciona un cuarto...</option>
                                                @php
                                                    $availableRooms = \App\Models\Room::whereDoesntHave('stays', function($q) {
                                                        $q->whereNull('discharge_date');
                                                    })->orderBy('number')->get();
                                                @endphp
                                                @foreach($availableRooms as $room)
                                                    <option value="{{ $room->id }}">Cuarto {{ $room->number }}</option>
                                                @endforeach
                                            </select>
                                            @if($availableRooms->isEmpty())
                                                <div class="alert alert-warning small mt-2 mb-0">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    No hay cuartos disponibles. Considera &ldquo;Atenci&oacute;n ambulatoria&rdquo; mientras se libera uno.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Diagn&oacute;stico inicial <span class="text-danger">*</span></label>
                                            <textarea name="diagnosis" rows="2" maxlength="500" class="form-control"
                                                      form="hospitalize-form-{{ $triage->id }}"
                                                      placeholder="Ej. Apendicitis aguda, Neumonía, etc."></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Talla (cm) <small class="text-muted">opcional</small></label>
                                            <input type="number" name="height_cm" min="30" max="250" class="form-control"
                                                   form="hospitalize-form-{{ $triage->id }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Peso (kg) <small class="text-muted">opcional</small></label>
                                            <input type="number" step="0.1" name="weight_kg" min="0.5" max="300" class="form-control"
                                                   form="hospitalize-form-{{ $triage->id }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                                <form id="hospitalize-form-{{ $triage->id }}" method="POST"
                                      action="{{ route('triage.hospitalize', $triage) }}" style="display:none;">
                                    @csrf
                                </form>
                                <form id="disposition-form-{{ $triage->id }}" method="POST"
                                      action="{{ route('triage.updateDisposition', $triage) }}" style="display:none;">
                                    @csrf
                                    <input type="hidden" name="disposition" id="disposition-value-{{ $triage->id }}">
                                </form>

                                <button type="button" class="btn btn-primary disposition-submit"
                                        data-triage-id="{{ $triage->id }}">
                                    <i class="bi bi-check"></i> Confirmar disposici&oacute;n
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .bg-orange { background-color: #fd7e14 !important; color: #000 !important; }
</style>

<script>
(function() {
    function updateWaitingTimes() {
        var elements = document.querySelectorAll('[data-waiting-time]');
        var now = Date.now();

        elements.forEach(function(el) {
            var startedAt = new Date(el.getAttribute('data-waiting-time')).getTime();
            var diffMin = Math.floor((now - startedAt) / 60000);

            var text;
            if (diffMin < 1) {
                text = 'hace menos de 1 min';
            } else if (diffMin < 60) {
                text = 'hace ' + diffMin + ' min';
            } else {
                var hours = Math.floor(diffMin / 60);
                var remaining = diffMin % 60;
                text = remaining === 0
                    ? 'hace ' + hours + 'h'
                    : 'hace ' + hours + 'h ' + remaining + 'min';
            }

            el.textContent = text;
        });
    }

    setInterval(updateWaitingTimes, 60000);
    updateWaitingTimes();

    // Mostrar/ocultar campos de hospitalización según radio seleccionado
    document.querySelectorAll('.disposition-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var modal = this.closest('.modal');
            var triageId = modal.id.replace('dispositionModal-', '');
            var fields = document.getElementById('hospitalize-fields-' + triageId);
            fields.style.display = this.value === 'hospitalized' ? 'block' : 'none';
        });
    });

    // Submit según disposición elegida
    document.querySelectorAll('.disposition-submit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var triageId = this.dataset.triageId;
            var modal = document.getElementById('dispositionModal-' + triageId);
            var selected = modal.querySelector('.disposition-radio:checked');

            if (!selected) {
                alert('Selecciona una disposición.');
                return;
            }

            if (selected.value === 'hospitalized') {
                var form = document.getElementById('hospitalize-form-' + triageId);
                var roomSelect = modal.querySelector('select[name="room_id"]');
                var diagnosisInput = modal.querySelector('textarea[name="diagnosis"]');

                if (!roomSelect || !roomSelect.value) {
                    alert('Selecciona un cuarto.');
                    if (roomSelect) roomSelect.focus();
                    return;
                }
                if (!diagnosisInput || !diagnosisInput.value.trim()) {
                    alert('El diagnóstico inicial es obligatorio.');
                    if (diagnosisInput) diagnosisInput.focus();
                    return;
                }

                form.submit();
            } else {
                var dForm = document.getElementById('disposition-form-' + triageId);
                document.getElementById('disposition-value-' + triageId).value = selected.value;
                dForm.submit();
            }
        });
    });
})();
</script>
@endsection
