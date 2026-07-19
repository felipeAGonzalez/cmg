@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('anesthesiaNotes.index', $stay) }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <h2 class="mb-0">
                <i class="bi bi-lungs"></i> Nota de Anestesia #{{ $note->id }}
                @if($note->surgery_urgency)
                    <span class="badge {{ $note->surgery_urgency === 'urgencia' ? 'bg-danger' : 'bg-info text-dark' }} ms-2">
                        {{ ucfirst($note->surgery_urgency) }}
                    </span>
                @endif
            </h2>
            <p class="text-muted mb-0 small">
                {{ $patient->fullName() }} — Estancia #{{ $stay->id }}
                @if($stay->room) — Hab. {{ $stay->room->number }} @endif
                &nbsp;·&nbsp; Registrada {{ $note->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('anesthesiaNotes.pdf', $note) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> PDF
            </a>
            @if($canEdit)
                <a href="{{ route('anesthesiaNotes.edit', $note) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @else
                <span class="badge bg-secondary align-self-center">Estancia dada de alta — solo lectura</span>
            @endif
        </div>
    </div>

    {{-- Datos básicos --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                @if($note->preop_diagnosis)
                    <div class="col-md-6">
                        <small class="text-muted">Dx prequirúrgico</small>
                        <p class="mb-0">{{ $note->preop_diagnosis }}</p>
                    </div>
                @endif
                @if($note->planned_surgery)
                    <div class="col-md-6">
                        <small class="text-muted">Cirugía programada</small>
                        <p class="mb-0">{{ $note->planned_surgery }}</p>
                    </div>
                @endif
                @if($note->asa_status)
                    <div class="col-md-3">
                        <small class="text-muted">ASA</small>
                        <p class="mb-0 fw-semibold">ASA {{ $note->asa_status }}</p>
                    </div>
                @endif
                @if($note->postSurgicalNote)
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="bi bi-link-45deg"></i>
                            Vinculada a Nota Postquirúrgica del {{ $note->postSurgicalNote->surgery_date?->format('d/m/Y') ?? '—' }}
                        </small>
                    </div>
                @endif
            </div>
            <hr class="my-2">
            <div class="row g-2">
                <div class="col-md-4">
                    <small class="text-muted">Médico / Anestesiólogo</small>
                    <p class="mb-0">{{ $note->attendingDoctor?->name ?? '—' }} {{ $note->attendingDoctor?->last_name_one }}</p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Creada por</small>
                    <p class="mb-0">{{ $note->createdBy?->name }} — {{ $note->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($note->updatedBy)
                    <div class="col-md-4">
                        <small class="text-muted">Última edición</small>
                        <p class="mb-0">{{ $note->updatedBy->name }} — {{ $note->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABS de visualización --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#show-valoracion"><i class="bi bi-clipboard-pulse"></i> Valoración</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#show-registro"><i class="bi bi-activity"></i> Registro</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#show-postanestesica"><i class="bi bi-heart-pulse"></i> Post Anestésica</a></li>
    </ul>

    <div class="tab-content">

        {{-- ======== VALORACIÓN ======== --}}
        <div class="tab-pane fade show active" id="show-valoracion">

            {{-- Antecedentes --}}
            @php
                $positiveAntecedents = collect($antecedents)->filter(function($label, $key) use ($note) {
                    return !empty($note->antecedents[$key]['has_condition'] ?? false);
                });
            @endphp
            @if($positiveAntecedents->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Antecedentes positivos</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($positiveAntecedents as $key => $label)
                                <div class="col-md-4">
                                    <span class="badge bg-warning text-dark me-1">{{ $label }}</span>
                                    @if(!empty($note->antecedents[$key]['evolution_time']))
                                        <small class="text-muted">{{ $note->antecedents[$key]['evolution_time'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @foreach([
                'current_medications'     => 'Medicamentos actuales',
                'previous_anesthesias'    => 'Anestesias previas',
                'other_antecedents'       => 'Otros antecedentes',
                'current_illness'         => 'Padecimiento actual',
            ] as $field => $label)
                @if(!empty(trim($note->{$field} ?? '')))
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>{{ $label }}</strong></div>
                        <div class="card-body"><p style="white-space:pre-wrap; margin:0;">{{ $note->{$field} }}</p></div>
                    </div>
                @endif
            @endforeach

            {{-- Exploración --}}
            @php
                $examFields = array_filter([
                    'Conciencia' => $note->consciousness_state ? ucfirst($note->consciousness_state) : null,
                    'Peso' => $note->weight_kg ? $note->weight_kg . ' kg' : null,
                    'Talla' => $note->height_m ? $note->height_m . ' m' : null,
                    'TA' => $note->exam_ta,
                    'FC' => $note->exam_fc,
                    'FR' => $note->exam_fr,
                    'Temp' => $note->exam_temp,
                ]);
            @endphp
            @if($examFields || $note->head_neck_exam || $note->airway_exam)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Exploración física</strong></div>
                    <div class="card-body">
                        @if($examFields)
                            <div class="row g-2 mb-3">
                                @foreach($examFields as $lbl => $val)
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted">{{ $lbl }}</small>
                                        <p class="mb-0 fw-semibold">{{ $val }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @foreach([
                            'head_neck_exam' => 'Cabeza y cuello', 'airway_exam' => 'Vía aérea',
                            'cardiopulmonary_exam' => 'Cardiopulmonar', 'abdomen_exam' => 'Abdomen',
                            'spine_exam' => 'Columna', 'extremities_exam' => 'Extremidades', 'other_exam' => 'Otros'
                        ] as $f => $l)
                            @if(!empty(trim($note->{$f} ?? '')))
                                <div class="mb-2">
                                    <small class="text-muted fw-semibold">{{ $l }}:</small>
                                    <span class="small"> {{ $note->{$f} }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Laboratorio --}}
            @php
                $labData = array_filter([
                    'Hb' => $note->lab_hb, 'Hto' => $note->lab_hto, 'TP' => $note->lab_tp,
                    'TPT' => $note->lab_tpt, 'Grupo/Rh' => $note->lab_blood_type_rh,
                    'Glucosa' => $note->lab_glucose, 'Urea' => $note->lab_urea, 'Creatinina' => $note->lab_creatinine,
                ]);
            @endphp
            @if($labData || $note->other_labs || $note->cabinet_studies)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Laboratorio y gabinete</strong></div>
                    <div class="card-body">
                        @if($labData)
                            <div class="row g-2 mb-2">
                                @foreach($labData as $lbl => $val)
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted">{{ $lbl }}</small>
                                        <p class="mb-0 fw-semibold">{{ $val }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($note->other_labs)
                            <p class="small mb-1"><strong>Otros:</strong> {{ $note->other_labs }}</p>
                        @endif
                        @if($note->cabinet_studies)
                            <p class="small mb-0"><strong>Gabinete:</strong> {{ $note->cabinet_studies }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @foreach(['anesthetic_plan' => 'Plan anestésico', 'preanesthetic_indications' => 'Indicaciones preanestésicas'] as $f => $l)
                @if(!empty(trim($note->{$f} ?? '')))
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>{{ $l }}</strong></div>
                        <div class="card-body"><p style="white-space:pre-wrap; margin:0;">{{ $note->{$f} }}</p></div>
                    </div>
                @endif
            @endforeach

        </div>{{-- /show-valoracion --}}

        {{-- ======== REGISTRO ======== --}}
        <div class="tab-pane fade" id="show-registro">

            {{-- Cirugía realizada --}}
            @php
                $hasRegData = $note->postop_diagnosis || $note->performed_surgery;
            @endphp
            @if($hasRegData)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Cirugía realizada</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($note->postop_diagnosis)
                                <div class="col-md-6">
                                    <small class="text-muted">Dx postoperatorio</small>
                                    <p style="white-space:pre-wrap; margin:0;">{{ $note->postop_diagnosis }}</p>
                                </div>
                            @endif
                            @if($note->performed_surgery)
                                <div class="col-md-6">
                                    <small class="text-muted">Cirugía realizada</small>
                                    <p style="white-space:pre-wrap; margin:0;">{{ $note->performed_surgery }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Equipo quirúrgico --}}
            <div class="card mb-3" style="border-left:3px solid #E91E63;">
                <div class="card-body">
                    <h6 class="mb-2" style="color:#E91E63;">Equipo quirúrgico</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <small class="text-muted">Cirujano</small>
                            <p class="mb-0">{{ $note->orSurgeonName() }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Ayudante / Instrumentista</small>
                            <p class="mb-0">{{ $note->orAssistantName() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Intubación y monitoreo --}}
            @php
                $hasIntubData = $note->intubation_blade || $note->intubation_cannula || $note->ventilation_notes;
            @endphp
            @if($hasIntubData || $note->continuous_ecg || $note->pulse_oximetry || $note->capnography)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Intubación y monitoreo</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @if($note->intubation_blade)
                                <div class="col-md-3"><small class="text-muted">Hoja laringo.</small><p class="mb-0">{{ $note->intubation_blade }}</p></div>
                            @endif
                            @if($note->intubation_cannula)
                                <div class="col-md-3"><small class="text-muted">Cánula</small><p class="mb-0">{{ $note->intubation_cannula }}</p></div>
                            @endif
                        </div>
                        @if($note->intubation_technical_difficulty)
                            <p class="small mb-1 mt-2 text-warning"><i class="bi bi-exclamation-triangle"></i> Dificultad técnica{{ $note->intubation_difficulty_detail ? ': ' . $note->intubation_difficulty_detail : '' }}</p>
                        @endif
                        <div class="d-flex gap-2 mt-2">
                            @if($note->continuous_ecg) <span class="badge bg-info text-dark">ECG continuo</span> @endif
                            @if($note->pulse_oximetry) <span class="badge bg-info text-dark">Oximetría de pulso</span> @endif
                            @if($note->capnography) <span class="badge bg-info text-dark">Capnografía</span> @endif
                        </div>
                        @if($note->ventilation_notes)
                            <p class="small mt-2 mb-0"><strong>Ventilación:</strong> {{ $note->ventilation_notes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Balance de líquidos --}}
            @php
                $fluidsIn  = array_filter(['Hartmann' => $note->fluids_in_hartmann, 'Glucosa' => $note->fluids_in_glucose, 'NaCl' => $note->fluids_in_nacl]);
                $fluidsOut = array_filter(['Diuresis' => $note->fluids_out_diuresis, 'Sangrado' => $note->fluids_out_bleeding, 'Pérd. insensibles' => $note->fluids_out_insensible_losses]);
            @endphp
            @if($fluidsIn || $fluidsOut)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Balance de líquidos</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($fluidsIn)
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">Ingresos</small>
                                    @foreach($fluidsIn as $lbl => $val)
                                        <span class="badge bg-success me-1">{{ $lbl }}: {{ $val }} ml</span>
                                    @endforeach
                                    <p class="small mt-1 mb-0">Total: <strong>{{ array_sum($fluidsIn) }} ml</strong></p>
                                </div>
                            @endif
                            @if($fluidsOut)
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">Egresos</small>
                                    @foreach($fluidsOut as $lbl => $val)
                                        <span class="badge bg-danger me-1">{{ $lbl }}: {{ $val }} ml</span>
                                    @endforeach
                                    <p class="small mt-1 mb-0">Total: <strong>{{ array_sum($fluidsOut) }} ml</strong></p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Aldrete OR --}}
            @if($note->aldrete_or_exit)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;">
                        <strong>Aldrete al salir de quirófano</strong>
                        <span class="badge bg-primary ms-2">Total: {{ $note->aldreteTotal($note->aldrete_or_exit) ?? '—' }}/10</span>
                    </div>
                    <div class="card-body">
                        @foreach(config('anesthesia_note_aldrete_scale') as $key => $criterion)
                            @if(isset($note->aldrete_or_exit[$key]))
                                <div class="mb-1 small">
                                    <strong>{{ $criterion['label'] }}:</strong>
                                    {{ $note->aldrete_or_exit[$key] }} —
                                    {{ $criterion['options'][$note->aldrete_or_exit[$key]] ?? '' }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tiempos --}}
            @php
                $times = array_filter([
                    'Inicio anestesia' => $note->anesthesia_start?->format('d/m/Y H:i'),
                    'Fin anestesia'    => $note->anesthesia_end?->format('d/m/Y H:i'),
                    'Inicio cirugía'   => $note->surgery_start?->format('d/m/Y H:i'),
                    'Fin cirugía'      => $note->surgery_end?->format('d/m/Y H:i'),
                    'Tiempo total'     => $note->anesthetic_time_total,
                    'Dosis total'      => $note->total_dose,
                ]);
            @endphp
            @if($times)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Tiempos quirúrgicos</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($times as $lbl => $val)
                                <div class="col-md-4 col-6">
                                    <small class="text-muted">{{ $lbl }}</small>
                                    <p class="mb-0 fw-semibold">{{ $val }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Signos vitales --}}
            @if($note->vitalReadings->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;">
                        <strong>Signos vitales transoperatorios</strong>
                        <small class="ms-2">({{ $note->vitalReadings->count() }} lecturas)</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hora</th><th>TA Sist.</th><th>TA Diast.</th>
                                        <th>FC</th><th>FR</th><th>Temp</th><th>SpO2</th><th>Evento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($note->vitalReadings as $r)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Str::substr($r->reading_time, 0, 5) }}</td>
                                            <td>{{ $r->ta_sys ?? '—' }}</td>
                                            <td>{{ $r->ta_dia ?? '—' }}</td>
                                            <td>{{ $r->fc ?? '—' }}</td>
                                            <td>{{ $r->fr ?? '—' }}</td>
                                            <td>{{ $r->temp ?? '—' }}</td>
                                            <td>{{ $r->spo2 !== null ? $r->spo2 . '%' : '—' }}</td>
                                            <td>{{ $r->event_marker ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Anestesia regional --}}
            @if($note->regional_anesthesia_type || $note->regional_agents_administered)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Anestesia regional</strong></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach(['Tipo' => $note->regional_anesthesia_type, 'Aguja' => $note->regional_needle, 'Nivel' => $note->regional_puncture_level] as $l => $v)
                                @if($v) <div class="col-md-4"><small class="text-muted">{{ $l }}</small><p class="mb-0">{{ $v }}</p></div> @endif
                            @endforeach
                            @if($note->regional_catheter) <div class="col-md-2"><span class="badge bg-info text-dark">Catéter</span></div> @endif
                            @if($note->regional_agents_administered)
                                <div class="col-12 mt-2">
                                    <small class="text-muted">Agentes administrados</small>
                                    <p style="white-space:pre-wrap; margin:0;">{{ $note->regional_agents_administered }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>{{-- /show-registro --}}

        {{-- ======== POST ANESTÉSICA ======== --}}
        <div class="tab-pane fade" id="show-postanestesica">

            @foreach([
                'anesthetic_technique_and_drugs' => 'Técnica anestésica y fármacos',
                'blood_fluids_administered'      => 'Hemoderivados y líquidos',
                'management_plan'                => 'Plan de manejo',
                'evolution_and_ucpa_discharge'   => 'Evolución y alta UCPA',
                'postop_pain_control'            => 'Control de dolor',
                'discharge_evolution'            => 'Evolución al alta',
                'discharge_indications'          => 'Indicaciones al alta',
            ] as $field => $label)
                @if(!empty(trim($note->{$field} ?? '')))
                    <div class="card mb-3">
                        <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>{{ $label }}</strong></div>
                        <div class="card-body"><p style="white-space:pre-wrap; margin:0;">{{ $note->{$field} }}</p></div>
                    </div>
                @endif
            @endforeach

            @if($note->incidents_or_accidents)
                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Se reportaron incidentes o accidentes durante el procedimiento.</div>
            @endif

            {{-- Aldrete UCPA ingreso --}}
            @if($note->aldrete_ucpa_admission)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;">
                        <strong>Aldrete — Ingreso UCPA</strong>
                        <span class="badge bg-primary ms-2">{{ $note->aldreteTotal($note->aldrete_ucpa_admission) ?? '—' }}/10</span>
                    </div>
                    <div class="card-body">
                        @foreach(config('anesthesia_note_aldrete_scale') as $key => $criterion)
                            @if(isset($note->aldrete_ucpa_admission[$key]))
                                <div class="mb-1 small">
                                    <strong>{{ $criterion['label'] }}:</strong>
                                    {{ $note->aldrete_ucpa_admission[$key] }} — {{ $criterion['options'][$note->aldrete_ucpa_admission[$key]] ?? '' }}
                                </div>
                            @endif
                        @endforeach
                        @php
                            $admVitals = array_filter(['TA' => $note->ucpa_admission_ta, 'FC' => $note->ucpa_admission_fc, 'FR' => $note->ucpa_admission_fr, 'SpO2' => $note->ucpa_admission_spo2]);
                        @endphp
                        @if($admVitals)
                            <div class="d-flex gap-3 mt-2">
                                @foreach($admVitals as $l => $v)<div><small class="text-muted">{{ $l }}</small><p class="mb-0 fw-semibold">{{ $v }}</p></div>@endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Aldrete UCPA alta --}}
            @if($note->aldrete_ucpa_discharge)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;">
                        <strong>Aldrete — Alta UCPA</strong>
                        <span class="badge bg-primary ms-2">{{ $note->aldreteTotal($note->aldrete_ucpa_discharge) ?? '—' }}/10</span>
                    </div>
                    <div class="card-body">
                        @foreach(config('anesthesia_note_aldrete_scale') as $key => $criterion)
                            @if(isset($note->aldrete_ucpa_discharge[$key]))
                                <div class="mb-1 small">
                                    <strong>{{ $criterion['label'] }}:</strong>
                                    {{ $note->aldrete_ucpa_discharge[$key] }} — {{ $criterion['options'][$note->aldrete_ucpa_discharge[$key]] ?? '' }}
                                </div>
                            @endif
                        @endforeach
                        @php
                            $disVitals = array_filter(['TA' => $note->ucpa_discharge_ta, 'FC' => $note->ucpa_discharge_fc, 'FR' => $note->ucpa_discharge_fr, 'SpO2' => $note->ucpa_discharge_spo2]);
                        @endphp
                        @if($disVitals)
                            <div class="d-flex gap-3 mt-2">
                                @foreach($disVitals as $l => $v)<div><small class="text-muted">{{ $l }}</small><p class="mb-0 fw-semibold">{{ $v }}</p></div>@endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Alta de anestesiología --}}
            @php
                $dischargeData = array_filter([
                    'TA' => $note->discharge_ta, 'Pulso' => $note->discharge_pulse,
                    'Resp.' => $note->discharge_resp,
                    'Conciencia' => $note->discharge_consciousness ? ucfirst($note->discharge_consciousness) : null,
                    'Diuresis' => $note->discharge_diuresis, 'Dolor' => $note->discharge_pain,
                ]);
            @endphp
            @if($dischargeData)
                <div class="card mb-3">
                    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;"><strong>Alta de anestesiología</strong></div>
                    <div class="card-body">
                        <div class="row g-2 mb-2">
                            @foreach($dischargeData as $l => $v)
                                <div class="col-md-3 col-6"><small class="text-muted">{{ $l }}</small><p class="mb-0 fw-semibold">{{ $v }}</p></div>
                            @endforeach
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($note->discharge_nausea) <span class="badge bg-warning text-dark">Náusea</span> @endif
                            @if($note->discharge_vomiting) <span class="badge bg-warning text-dark">Vómito</span> @endif
                            @if($note->discharge_headache) <span class="badge bg-warning text-dark">Cefálea</span> @endif
                            @if($note->discharge_ambulation) <span class="badge bg-success">Deambula</span> @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>{{-- /show-postanestesica --}}

    </div>{{-- /tab-content --}}
</div>
@endsection
