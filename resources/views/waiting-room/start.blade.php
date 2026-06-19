@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('waitingRoom.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h2 class="mb-0">Iniciar hoja de triage</h2>
    </div>

    <p class="text-muted">
        Selecciona c&oacute;mo es el paciente que llega al CMG:
    </p>

    <div class="row g-3">
        {{-- Paciente nuevo --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="bi bi-person-plus" style="font-size:3rem; color:#0d6efd;"></i>
                    <h5 class="mt-3">Paciente nuevo</h5>
                    <p class="text-muted small flex-grow-1">
                        Es la primera vez que el paciente viene al CMG.
                        Se capturar&aacute;n todos sus datos.
                    </p>
                    <a href="{{ route('patients.create') }}?return_to=triage"
                       class="btn btn-primary">
                        Registrar paciente nuevo
                    </a>
                </div>
            </div>
        </div>

        {{-- Paciente conocido --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="text-center">
                        <i class="bi bi-search" style="font-size:3rem; color:#198754;"></i>
                        <h5 class="mt-3">Paciente conocido</h5>
                        <p class="text-muted small">
                            El paciente ya ha venido antes. Busca por nombre.
                        </p>
                    </div>

                    <div class="position-relative mt-2 flex-grow-1">
                        <input type="text" id="patient-search"
                               placeholder="Nombre, apellido o fecha (DD/MM/YYYY)..."
                               class="form-control" autocomplete="off">
                        <div id="patient-search-results"
                             class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                             style="z-index:1000; max-height:300px; overflow-y:auto; display:none;">
                        </div>
                        <div id="patient-search-status" class="small text-muted mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paciente sin identificar --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="bi bi-person-exclamation" style="font-size:3rem; color:#fd7e14;"></i>
                    <h5 class="mt-3">Paciente sin identificar</h5>
                    <p class="text-muted small flex-grow-1">
                        Paciente urgente sin datos disponibles (inconsciente,
                        no puede comunicarse, etc.). Se completar&aacute;n los datos despu&eacute;s.
                    </p>
                    <a href="{{ route('patients.create') }}?return_to=triage&unidentified=1"
                       class="btn btn-warning">
                        Crear registro sin identificar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var input = document.getElementById('patient-search');
    var resultsBox = document.getElementById('patient-search-results');
    var statusEl = document.getElementById('patient-search-status');
    var debounceTimer = null;

    input.addEventListener('input', function() {
        var query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            resultsBox.style.display = 'none';
            statusEl.textContent = '';
            return;
        }

        statusEl.textContent = 'Buscando...';

        debounceTimer = setTimeout(function() {
            fetch('{{ route("triage.patients.search") }}?q=' + encodeURIComponent(query), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) { renderResults(data.results || []); })
            .catch(function() {
                statusEl.textContent = 'Error en la búsqueda. Intenta de nuevo.';
                resultsBox.style.display = 'none';
            });
        }, 300);
    });

    function renderResults(patients) {
        if (patients.length === 0) {
            resultsBox.innerHTML = '<div class="p-3 text-muted small">' +
                'No se encontraron pacientes. ¿Es paciente nuevo?</div>';
            resultsBox.style.display = 'block';
            statusEl.textContent = '';
            return;
        }

        var html = patients.map(function(p) {
            var ageText = p.age !== null ? p.age + ' años · ' : '';
            var birthText = p.birth_date || '';
            return '<a href="/triage/create/' + p.id + '" ' +
                'class="d-block p-2 border-bottom text-decoration-none text-dark patient-result">' +
                '<strong>' + escapeHtml(p.full_name) + '</strong>' +
                '<div class="small text-muted">' + ageText + birthText + '</div></a>';
        }).join('');

        resultsBox.innerHTML = html;
        resultsBox.style.display = 'block';
        statusEl.textContent = patients.length + ' coincidencias';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });

    input.addEventListener('focus', function() {
        if (resultsBox.innerHTML.trim() !== '' && this.value.trim().length >= 2) {
            resultsBox.style.display = 'block';
        }
    });
})();
</script>

<style>
    .patient-result:hover { background-color: #f8f9fa; }
</style>
@endsection
