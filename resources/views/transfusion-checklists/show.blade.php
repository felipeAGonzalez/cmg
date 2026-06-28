@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Encabezado --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('transfusionChecklists.index', $stay) }}"
           class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0">
                Transfusión #{{ $checklist->id }}
                <span class="badge {{ $checklist->statusBadgeClass() }} fs-6">
                    {{ $checklist->statusLabel() }}
                </span>
            </h2>
            <p class="text-muted mb-0">
                Paciente: <strong>{{ $patient->fullName() }}</strong>
                · Iniciada: {{ $checklist->started_at->format('d/m/Y H:i') }}
                @if($checklist->finalized_at)
                    · Finalizada: {{ $checklist->finalized_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-1">
            @if($stay->discharge_date === null)
                <a href="{{ route('transfusionChecklists.edit', $checklist) }}"
                   class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endif
            @if($checklist->isFinalized())
                <a href="{{ route('transfusionChecklists.pdf', $checklist) }}"
                   target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Datos del paciente --}}
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Datos del paciente</h6>
        </div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6">
                    <strong>Nombre:</strong> {{ $patient->fullName() }}
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong> {{ ucfirst(strtolower($patient->gender ?? '—')) }}
                </div>
                <div class="col-md-3">
                    <strong>Edad:</strong>
                    @if($patient->birth_date)
                        {{ \Carbon\Carbon::parse($patient->birth_date)->age }} años
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Habitación:</strong> {{ $stay->room->number ?? '—' }}
                </div>
                <div class="col-md-3">
                    <strong>Expediente:</strong> {{ $patient->id }}
                </div>
                @if($checklist->folio)
                <div class="col-md-6">
                    <strong>Folio:</strong> {{ $checklist->folio }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @php
        $check = fn($val) => $val ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>';
        $radioLabel = fn($val, $options) => $options[$val] ?? '<span class="text-muted">—</span>';
    @endphp

    {{-- SECCIÓN 1: ENTRADA --}}
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">1. ENTRADA <small>(antes de transfundir)</small></h5>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <p class="fw-bold">El médico y el personal de enfermería, con el paciente confirma:</p>
                <div>{!! $check($checklist->entry_identity_confirmed) !!} Su identidad</div>
                <div>{!! $check($checklist->entry_indication_confirmed) !!} Indicación de la transfusión</div>
                <div>{!! $check($checklist->entry_product_confirmed) !!} Producto a transfundir</div>
                <div>{!! $check($checklist->entry_consent_confirmed) !!} Su consentimiento informado</div>
            </div>

            <div class="mb-4">
                <p class="fw-bold">¿La vía para transfundir es única?</p>
                <div>{!! $check($checklist->entry_via_unique) !!} Sí &nbsp;&nbsp; {!! $check($checklist->entry_via_permeable) !!} Permeable</div>
            </div>

            <div class="mb-4">
                <div>{!! $check($checklist->entry_asepsis_done) !!} Se realizó la asepsia de sitio</div>
            </div>

            <div class="mb-4">
                <p class="fw-bold">Se completó el control de la seguridad de la transfusión al revisar...</p>
                <div>{!! $check($checklist->entry_check_flebotech) !!} Flebotech</div>
                <div>{!! $check($checklist->entry_check_availability) !!} La disponibilidad de la sangre o hemoderivado</div>
                <div>{!! $check($checklist->entry_check_transport) !!} El traslado adecuado del producto</div>
                <div>{!! $check($checklist->entry_check_vitals) !!} Corrobora signos vitales previamente</div>
            </div>

            <div class="mb-4">
                <div>{!! $check($checklist->entry_equipment_ok) !!} Se colocó y se comprobó que funcione correctamente el equipo para transfusión</div>
            </div>

            <div>
                <p class="fw-bold">¿Tiene el paciente...?</p>
                <div class="ms-3 mb-2">
                    <strong>Alergias conocidas:</strong>
                    {!! $radioLabel($checklist->entry_allergies, ['no' => 'No', 'yes' => 'Sí']) !!}
                    @if($checklist->entry_allergies === 'yes' && $checklist->entry_allergies_detail)
                        — {{ $checklist->entry_allergies_detail }}
                    @endif
                </div>
                <div class="ms-3 mb-2">
                    <strong>Antecedente de reacciones previas:</strong>
                    {!! $radioLabel($checklist->entry_previous_reactions, ['no' => 'No', 'yes_doctor_aware' => 'Sí, y el médico está enterado']) !!}
                </div>
                <div class="ms-3 mb-2">
                    <strong>Riesgo de hemorragia:</strong>
                    {!! $radioLabel($checklist->entry_bleeding_risk, ['no' => 'No', 'yes_with_access' => 'Sí, con vías colocadas']) !!}
                </div>
                <div class="ms-3">
                    <strong>Hemoderivados y soluciones disponibles:</strong>
                    {!! $radioLabel($checklist->entry_blood_products_available, ['no' => 'No', 'yes_crossmatched' => 'Sí, con cruce previo']) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 2: PAUSA --}}
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">2. PAUSA <small>(justo antes de iniciar)</small></h5>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <p class="fw-bold">Se verifica que el médico ó la enfermera se presente por su nombre y su función:</p>
                <div>{!! $check($checklist->pause_doctor_on_duty_present) !!} Médico de guardia</div>
                <div>{!! $check($checklist->pause_anesthesiologist_present) !!} Anestesiólogo</div>
                <div>{!! $check($checklist->pause_nurse_present) !!} Personal de Enfermería</div>
            </div>

            <div class="mb-4">
                <p class="fw-bold">Confirmación verbal e individual:</p>
                <div>{!! $check($checklist->pause_identity_verified) !!} La identidad del paciente</div>
                <div>{!! $check($checklist->pause_indication_verified) !!} Indicación de la transfusión</div>
                <div>{!! $check($checklist->pause_access_verified) !!} Vía de acceso única y permeable</div>
                <div>{!! $check($checklist->pause_product_verified) !!} Producto a transfundir</div>
            </div>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Datos del producto</h6>
                    <div class="row g-2">
                        <div class="col-md-3"><strong>Grupo:</strong> {{ $checklist->product_group ?? '—' }}</div>
                        <div class="col-md-3"><strong>Factor RH:</strong> {{ $checklist->product_rh_factor ?? '—' }}</div>
                        <div class="col-md-3"><strong>Folio:</strong> {{ $checklist->product_folio ?? '—' }}</div>
                        <div class="col-md-3"><strong>Cantidad:</strong> {{ $checklist->product_quantity ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Tipo de producto</h6>
                    <div class="mb-2"><strong>Volumen total:</strong> {{ $checklist->product_volume_total ?? '—' }} ml</div>
                    @php
                        $productTypes = [
                            ['product_red_cells', 'product_red_cells_amount', 'Concentrado Eritrocitario'],
                            ['product_fresh_plasma', 'product_fresh_plasma_amount', 'Plasma fresco'],
                            ['product_platelet_concentrate', 'product_platelet_concentrate_amount', 'Concentrado plaquetario'],
                            ['product_cryoprecipitate', 'product_cryoprecipitate_amount', 'Crioprecipitado'],
                            ['product_factor_vii', 'product_factor_vii_amount', 'Factor VII'],
                            ['product_apheresis', 'product_apheresis_amount', 'Aféresis'],
                        ];
                    @endphp
                    @foreach($productTypes as [$checkField, $amountField, $label])
                        @if($checklist->{$checkField})
                            <div>{!! $check(true) !!} {{ $label }} {{ $checklist->{$amountField} ? '— ' . $checklist->{$amountField} . ' ml' : '' }}</div>
                        @endif
                    @endforeach
                    @if($checklist->product_other)
                        <div><strong>Otro:</strong> {{ $checklist->product_other }} {{ $checklist->product_other_amount ? '— ' . $checklist->product_other_amount . ' ml' : '' }}</div>
                    @endif
                </div>
            </div>

            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Signos vitales (pre-transfusión)</h6>
                    <div class="row g-2">
                        <div class="col-md-3"><strong>FC:</strong> {{ $checklist->pause_vitals_fc ?? '—' }}</div>
                        <div class="col-md-3"><strong>TA:</strong> {{ $checklist->pause_vitals_ta ?? '—' }}</div>
                        <div class="col-md-3"><strong>TEMP:</strong> {{ $checklist->pause_vitals_temp ?? '—' }}</div>
                        <div class="col-md-3"><strong>FR:</strong> {{ $checklist->pause_vitals_fr ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 3: DURANTE Y SALIDA --}}
    <div class="card mb-3 border-success">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">3. DURANTE Y SALIDA</h5>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <p class="fw-bold">Con el médico de guardia, el anestesiólogo o el personal de enfermería:</p>
                <div>{!! $check($checklist->during_monitoring_done) !!} El responsable monitoriza al paciente durante la transfusión</div>
                <div>{!! $check($checklist->during_vitals_monitored) !!} Signos vitales</div>
                <div>{!! $check($checklist->during_adverse_reactions_monitored) !!} Reacciones adversas</div>
                <div>{!! $check($checklist->during_duration_monitored) !!} Duración de la transfusión</div>
                <div>{!! $check($checklist->during_via_permeability_monitored) !!} Permeabilidad de la vía</div>
            </div>

            <div class="mb-4">
                <p class="fw-bold">Al terminar la transfusión:</p>
                <div>{!! $check($checklist->exit_vitals_confirmed) !!} Signos vitales</div>
                <div>{!! $check($checklist->exit_logbook_filled) !!} Llenado correcto de la libreta de transfusión</div>
                <div>{!! $check($checklist->exit_bag_disposed) !!} Desecha la bolsa con el equipo en contenedor de RPBI</div>
            </div>

            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Eventos adversos</h6>
                    <div class="mb-2">
                        <strong>¿Ocurrieron eventos adversos?</strong>
                        {{ $checklist->adverse_events_occurred ? 'Sí' : 'No' }}
                        @if($checklist->adverse_events_occurred && $checklist->adverse_events_detail)
                            — {{ $checklist->adverse_events_detail }}
                        @endif
                    </div>
                    @if($checklist->adverse_events_occurred)
                    <div>
                        <strong>¿Se registró?</strong>
                        {{ $checklist->adverse_events_registered ? 'Sí' : 'No' }}
                        @if($checklist->adverse_events_registered && $checklist->adverse_events_register_location)
                            — {{ $checklist->adverse_events_register_location }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Trazabilidad --}}
    <div class="card mb-4">
        <div class="card-body small text-muted">
            <strong>Creado por:</strong> {{ $checklist->createdBy->fullName() }}
            · {{ $checklist->created_at->format('d/m/Y H:i') }}
            @if($checklist->updatedBy)
                <br><strong>Última modificación:</strong> {{ $checklist->updatedBy->fullName() }}
                · {{ $checklist->updated_at->format('d/m/Y H:i') }}
            @endif
        </div>
    </div>
</div>
@endsection
