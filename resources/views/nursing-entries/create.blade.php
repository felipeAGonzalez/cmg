@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-journal-plus me-2"></i>Nuevo registro — Cuarto {{ $stay->room->number }}
        </h4>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
        <div class="small">
            <span class="text-muted">Paciente:</span> <strong>{{ $stay->patient->fullName() }}</strong>
            <span class="text-muted ms-2">· {{ $stay->patient->age() }} años</span>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('nursingEntries.store', $stay) }}">
        @csrf

        <div class="card border-0 shadow-sm p-4 mb-4">

            {{-- Categoría --}}
            <div class="mb-3">
                <label for="category" class="form-label fw-semibold">Categoría</label>
                <select id="category" name="category"
                        class="form-select @error('category') is-invalid @enderror" required>
                    <option value="">— Selecciona —</option>
                    @foreach($categories as $key => $cfg)
                        <option value="{{ $key }}"
                                data-description="{{ $cfg['description'] }}"
                                {{ old('category', $preselectedCategory) === $key ? 'selected' : '' }}>
                            {{ $cfg['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div id="categoryDescription" class="form-text mt-2" style="display:none;"></div>
            </div>

            {{-- Hora del registro --}}
            <div class="mb-3">
                <label for="recorded_at" class="form-label fw-semibold">Hora del registro</label>
                <input type="datetime-local" id="recorded_at" name="recorded_at"
                       class="form-control @error('recorded_at') is-invalid @enderror"
                       value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}"
                       max="{{ now()->format('Y-m-d\TH:i') }}" required>
                <div class="form-text">Usa la hora real del evento (no puede ser futura).</div>
                @error('recorded_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Descripción --}}
            <div class="mb-1">
                <label for="description" class="form-label fw-semibold">Descripción</label>
                <textarea id="description" name="description" rows="4" minlength="3" maxlength="2000"
                          class="form-control @error('description') is-invalid @enderror"
                          required>{{ old('description') }}</textarea>
                <div id="descriptionHelp" class="form-text">Describe el registro de enfermería.</div>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar</button>
            <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const select      = document.getElementById('category');
        const descBox     = document.getElementById('categoryDescription');
        const helpText    = document.getElementById('descriptionHelp');

        // Texto de ayuda del textarea según la categoría seleccionada.
        const helpByCategory = {
            'treatment': 'Describe el tratamiento aplicado: tipo, zona, materiales usados, etc.',
            'symptom': 'Describe el signo o síntoma observado.',
            'assistive_measure': 'Describe la medida asistencial realizada.',
            'evolution_note': 'Describe la evolución y respuesta del paciente.',
            'observation': 'Anota la observación puntual.',
        };

        const apply = () => {
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) {
                descBox.style.display = 'none';
                descBox.textContent = '';
                helpText.textContent = 'Describe el registro de enfermería.';
                return;
            }
            descBox.textContent = opt.dataset.description || '';
            descBox.style.display = opt.dataset.description ? 'block' : 'none';
            helpText.textContent = helpByCategory[opt.value] || 'Describe el registro de enfermería.';
        };

        select.addEventListener('change', apply);
        apply(); // estado inicial (respeta la categoría preseleccionada)
    })();
</script>
@endpush
