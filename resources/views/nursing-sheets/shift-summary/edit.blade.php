@extends('layouts.app')

@php use App\Support\Shift; @endphp

@section('content')
<div class="container py-4" style="max-width:820px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-card-checklist me-2"></i>Resumen del turno
            {{ Shift::label($shiftInfo['shift']) }} — {{ $shiftInfo['shift_date']->format('d/m/Y') }}
        </h4>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bi bi-info-circle me-1"></i>
        Este resumen solo se puede editar durante el turno en curso
        ({{ Shift::timeRange($shiftInfo['shift']) }}).
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('shiftSummary.update', $stay) }}" id="summaryForm">
        @csrf
        @method('PUT')

        <div class="accordion mb-4" id="summaryAccordion">

            {{-- 1. Alimentación --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec1">
                        <i class="bi bi-egg-fried me-2"></i>Alimentación
                    </button>
                </h2>
                <div id="sec1" class="accordion-collapse collapse show" data-bs-parent="#summaryAccordion">
                    <div class="accordion-body row g-3">
                        <div class="col-md-6">
                            <label for="diet" class="form-label fw-semibold">Dieta</label>
                            <input type="text" maxlength="100" id="diet" name="diet"
                                   class="form-control" value="{{ old('diet', $summary->diet) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="formula" class="form-label fw-semibold">Fórmula</label>
                            <input type="text" maxlength="100" id="formula" name="formula"
                                   class="form-control" value="{{ old('formula', $summary->formula) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Hidratación --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec2">
                        <i class="bi bi-droplet me-2"></i>Hidratación
                    </button>
                </h2>
                <div id="sec2" class="accordion-collapse collapse" data-bs-parent="#summaryAccordion">
                    <div class="accordion-body row g-3">
                        <div class="col-md-6">
                            <label for="oral_liquids_ml" class="form-label fw-semibold">Líquidos orales (ml)</label>
                            <input type="number" min="0" max="10000" id="oral_liquids_ml" name="oral_liquids_ml"
                                   class="form-control" value="{{ old('oral_liquids_ml', $summary->oral_liquids_ml) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="parenteral_liquids_ml" class="form-label fw-semibold">Líquidos parenterales (ml)</label>
                            <input type="number" min="0" max="10000" id="parenteral_liquids_ml" name="parenteral_liquids_ml"
                                   class="form-control" value="{{ old('parenteral_liquids_ml', $summary->parenteral_liquids_ml) }}">
                        </div>
                        <div class="col-12">
                            <label for="electrolytes_blood_elements" class="form-label fw-semibold">Electrólitos / elementos sanguíneos</label>
                            <textarea id="electrolytes_blood_elements" name="electrolytes_blood_elements" rows="2" maxlength="2000"
                                      class="form-control">{{ old('electrolytes_blood_elements', $summary->electrolytes_blood_elements) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Eliminaciones --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec3">
                        <i class="bi bi-arrow-repeat me-2"></i>Eliminaciones
                    </button>
                </h2>
                <div id="sec3" class="accordion-collapse collapse" data-bs-parent="#summaryAccordion">
                    <div class="accordion-body row g-3">
                        <div class="col-md-4">
                            <label for="urine_output_ml" class="form-label fw-semibold">Uresis (ml)</label>
                            <input type="number" min="0" max="10000" id="urine_output_ml" name="urine_output_ml"
                                   class="form-control" value="{{ old('urine_output_ml', $summary->urine_output_ml) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="evacuations_count" class="form-label fw-semibold">Evacuaciones</label>
                            <input type="number" min="0" max="1500" id="evacuations_count" name="evacuations_count"
                                   class="form-control" value="{{ old('evacuations_count', $summary->evacuations_count) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="vomit_ml" class="form-label fw-semibold">Vómito (ml)</label>
                            <input type="number" min="0" max="10000" id="vomit_ml" name="vomit_ml"
                                   class="form-control @error('vomit_ml') is-invalid @enderror"
                                   value="{{ old('vomit_ml', $summary->vomit_ml ?? 0) }}">
                            @error('vomit_ml')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="aspiration_ml" class="form-label fw-semibold">Aspiración (ml)</label>
                            <input type="number" min="0" max="10000" id="aspiration_ml" name="aspiration_ml"
                                   class="form-control @error('aspiration_ml') is-invalid @enderror"
                                   value="{{ old('aspiration_ml', $summary->aspiration_ml ?? 0) }}">
                            @error('aspiration_ml')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="drainage_ml" class="form-label fw-semibold">Drenaje (ml)</label>
                            <input type="number" min="0" max="10000" id="drainage_ml" name="drainage_ml"
                                   class="form-control @error('drainage_ml') is-invalid @enderror"
                                   value="{{ old('drainage_ml', $summary->drainage_ml ?? 0) }}">
                            @error('drainage_ml')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @php
                            $drainageValue = (int) old('drainage_ml', $summary->drainage_ml ?? 0);
                            $hasDrainage   = $drainageValue > 0;
                        @endphp
                        <div class="col-md-12" id="drainage-type-block"
                             style="display: {{ $hasDrainage ? 'block' : 'none' }};">
                            <label for="drainage_type" class="form-label fw-semibold">Tipo de drenaje</label>
                            <input type="text" id="drainage_type" name="drainage_type" maxlength="200"
                                   class="form-control @error('drainage_type') is-invalid @enderror"
                                   value="{{ old('drainage_type', $summary->drainage_type) }}"
                                   placeholder="Ej. Penrose, Jackson-Pratt, etc.">
                            @error('drainage_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('drainage_ml').addEventListener('input', function () {
                    var block = document.getElementById('drainage-type-block');
                    block.style.display = parseInt(this.value, 10) > 0 ? 'block' : 'none';
                });
            </script>

            {{-- 4. Estudios --}}
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec4">
                        <i class="bi bi-clipboard2-data me-2"></i>Estudios
                    </button>
                </h2>
                <div id="sec4" class="accordion-collapse collapse" data-bs-parent="#summaryAccordion">
                    <div class="accordion-body row g-3">
                        <div class="col-12">
                            <label for="lab_biological_products" class="form-label fw-semibold">Laboratorio / productos biológicos</label>
                            <textarea id="lab_biological_products" name="lab_biological_products" rows="2" maxlength="2000"
                                      class="form-control">{{ old('lab_biological_products', $summary->lab_biological_products) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="reagents" class="form-label fw-semibold">Reactivos</label>
                            <textarea id="reagents" name="reagents" rows="2" maxlength="2000"
                                      class="form-control">{{ old('reagents', $summary->reagents) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="studies_operations" class="form-label fw-semibold">Estudios / operaciones</label>
                            <textarea id="studies_operations" name="studies_operations" rows="2" maxlength="2000"
                                      class="form-control">{{ old('studies_operations', $summary->studies_operations) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-1"></i>
            Las observaciones puntuales se registran ahora en la sección
            <strong>Notas y registros</strong> del módulo de Hojas de Enfermería.
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar resumen</button>
            <a href="{{ route('nursingSheets.index', $stay) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('summaryForm');
    if (!form) return;

    form.querySelectorAll('input, textarea').forEach(function (el) {
        // Mensajes claros en español según el tipo de error de validación.
        el.addEventListener('invalid', function () {
            const v = el.validity;
            if (v.valueMissing)        el.setCustomValidity('Este campo es obligatorio.');
            else if (v.rangeUnderflow) el.setCustomValidity('El valor mínimo permitido es ' + el.min + '.');
            else if (v.rangeOverflow)  el.setCustomValidity('El valor máximo permitido es ' + el.max + '.');
            else if (v.stepMismatch)   el.setCustomValidity('Ingresa un número entero válido (sin decimales).');
            else if (v.badInput)       el.setCustomValidity('Ingresa un número válido.');
            else if (v.tooLong)        el.setCustomValidity('El texto es demasiado largo.');
            else                       el.setCustomValidity('');
        });
        // Limpia el mensaje al corregir, para revalidar correctamente.
        el.addEventListener('input', function () { el.setCustomValidity(''); });
    });

    // Si el campo inválido está en una sección colapsada, ábrela para que sea
    // visible y enfocable (de lo contrario el navegador no puede mostrar el aviso).
    // 'invalid' no propaga, por eso se escucha en fase de captura.
    form.addEventListener('invalid', function (e) {
        const panel = e.target.closest('.accordion-collapse');
        if (panel && !panel.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(panel).show();
        }
    }, true);
});
</script>
@endpush
