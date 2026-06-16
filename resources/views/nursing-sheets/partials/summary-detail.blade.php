{{--
    Detalle de solo lectura de un resumen de turno.
    Variable esperada: $summary (ShiftSummary)
--}}
@php
    $fields = [
        'Dieta'                          => $summary->diet,
        'Fórmula'                        => $summary->formula,
        'Líquidos orales (ml)'           => $summary->oral_liquids_ml,
        'Líquidos parenterales (ml)'     => $summary->parenteral_liquids_ml,
        'Electrólitos / elementos sang.' => $summary->electrolytes_blood_elements,
        'Uresis (ml)'                    => $summary->urine_output_ml,
        'Evacuaciones'                   => $summary->evacuations_count,
        'Vómito (ml)'     => $summary->vomit_ml,
        'Aspiración (ml)' => $summary->aspiration_ml,
        'Drenaje (ml)'    => $summary->drainage_ml . ($summary->drainage_ml > 0 && $summary->drainage_type
                                ? ' (' . $summary->drainage_type . ')' : ''),
        'Lab / productos biológicos' => $summary->lab_biological_products,
        'Reactivos'                      => $summary->reagents,
        'Estudios / operaciones'         => $summary->studies_operations,
    ];
    $hasData = collect($fields)->contains(fn ($v) => $v !== null && $v !== '');
@endphp

@if(! $hasData)
    <p class="text-muted fst-italic mb-0">Sin resumen de turno capturado.</p>
@else
    <dl class="row mb-0 small">
        @foreach($fields as $label => $value)
            @if($value !== null && $value !== '')
                <dt class="col-sm-4 text-muted fw-normal">{{ $label }}</dt>
                <dd class="col-sm-8">{{ $value }}</dd>
            @endif
        @endforeach
    </dl>
@endif
