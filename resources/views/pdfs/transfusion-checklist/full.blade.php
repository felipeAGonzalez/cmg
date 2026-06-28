@extends('pdfs.layouts.base')

@section('document-title', '')
@section('document-subtitle', '')

@section('content')
<style>
    @page { margin: 50px 15px 25px 15px; }
    .doc-title { display:none; }
    
    /* Espaciado proporcional: más arriba de los strong (separa sub-secciones) */
    .col-content strong { display:inline-block; margin-top:5px; }
    .col-content > strong:first-child { margin-top:0; }
    
    /* Checkbox vacío con altura uniforme: clave para que no se vea roto */
    .cb {
        display:inline-block;
        border:1px solid #333;
        min-width:11px;
        height:9px;
        line-height:9px;
        padding:0 2px;
        text-align:center;
        font-size:6.5pt;
        vertical-align:middle;
    }
</style>

@php
    $g        = strtoupper($patient->gender ?? '');
    $isMale   = $g === 'M' || $g === 'MASCULINO';
    $isFemale = $g === 'F' || $g === 'FEMENINO';
    $age      = $patient->birth_date
        ? \Carbon\Carbon::parse($patient->birth_date)->age
        : null;

    // Helper actualizado: usa clase CSS para uniformidad
    $cb = function ($value) {
        return '<span class="cb">' . ($value ? 'X' : '&nbsp;') . '</span>';
    };

    $fmt = fn ($v) => $v !== null
        ? rtrim(rtrim(number_format((float) $v, 1), '0'), '.')
        : '';

    $sep = '<div style="border-top:1px dashed #ccc;margin:5px 0 3px 0;"></div>';
@endphp

{{-- ============================================== --}}
{{-- Encabezado institucional rosa                  --}}
{{-- ============================================== --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:3px;">
    <tr>
        <td style="width:65px;vertical-align:middle;padding-right:5px;">
            @php
                $logoPath = public_path('logos/CMG.png');
                $logoData = is_file($logoPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                    : null;
            @endphp
            @if($logoData)
                <img src="{{ $logoData }}" style="max-height:32px;max-width:60px;" alt="CMG">
            @else
                <div style="font-size:6pt;color:#aaa;text-align:center;">[LOGO]</div>
            @endif
        </td>
        <td style="vertical-align:middle;">
            <div style="background-color:#E91E63;color:white;
                        padding:3px 8px;font-weight:bold;font-size:9pt;
                        text-align:center;letter-spacing:.5px;">
                LISTA DE VERIFICACIÓN DE TRANSFUSIÓN SEGURA
            </div>
        </td>
        <td style="width:78px;vertical-align:middle;padding-left:5px;">
            <div style="border:1px solid #E91E63;padding:2px 4px;
                        font-size:7pt;text-align:center;">
                <strong>FOLIO</strong><br>
                <span style="font-size:8pt;">{{ $checklist->folio ?? '' }}</span>
            </div>
        </td>
    </tr>
</table>

<div style="text-align:right;font-size:6pt;color:#555;margin-bottom:3px;line-height:1.2;">
    Privada Solar #3 Zona Centro Acámbaro, GTO. | C.P. 38600 | Tel. 01 (417) 172 04 30 y 172 81 30
</div>

{{-- Datos del paciente --}}
<table style="width:100%;border:1px solid #333;border-collapse:collapse;
              margin-bottom:5px;font-size:7pt;">
    <tr>
        <td style="padding:2px 5px;border-right:1px solid #bbb;">
            <strong>NOMBRE:</strong> {{ $patient->fullName() }}
        </td>
        <td style="padding:2px 5px;border-right:1px solid #bbb;width:10%;">
            <strong>EDAD:</strong> {{ $age !== null ? $age . ' años' : '—' }}
        </td>
        <td style="padding:2px 5px;border-right:1px solid #bbb;width:13%;">
            <strong>SEXO:</strong>
            <span class="cb">{!! $isMale ? 'X' : '&nbsp;' !!}</span> M
            &nbsp;
            <span class="cb">{!! $isFemale ? 'X' : '&nbsp;' !!}</span> F
        </td>
        <td style="padding:2px 5px;border-right:1px solid #bbb;width:13%;">
            <strong>F. NAC:</strong>
            {{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') : '—' }}
        </td>
        <td style="padding:2px 5px;border-right:1px solid #bbb;width:8%;">
            <strong>HAB:</strong> {{ $stay->room->number ?? '—' }}
        </td>
        <td style="padding:2px 5px;width:14%;">
            <strong>FECHA:</strong> {{ $checklist->started_at->format('d/m/Y H:i') }}
        </td>
    </tr>
</table>

{{-- ============================================== --}}
{{-- UNA tabla, UNA fila, TRES celdas               --}}
{{-- ============================================== --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:5px;">
    <tr>

        {{-- ══════════════════════════ --}}
        {{-- CELDA 1: ENTRADA           --}}
        {{-- ══════════════════════════ --}}
        <td style="width:33%;vertical-align:top;padding-right:3px;">
            <div style="background-color:#E91E63;color:white;padding:2px 5px;
                        font-weight:bold;font-size:8pt;text-align:center;">
                1. ENTRADA
            </div>
            <div class="col-content" style="border:1px solid #333;border-top:none;padding:4px 6px;
                        font-size:6.5pt;line-height:1.5;">

                <strong>Confirman con el paciente:</strong><br>
                {!! $cb($checklist->entry_identity_confirmed) !!} Identidad<br>
                {!! $cb($checklist->entry_indication_confirmed) !!} Indicación de la transfusión<br>
                {!! $cb($checklist->entry_product_confirmed) !!} Producto a transfundir<br>
                {!! $cb($checklist->entry_consent_confirmed) !!} Consentimiento informado
                {!! $sep !!}
                <strong>¿Vía única?</strong>
                {!! $cb($checklist->entry_via_unique) !!} Sí
                &nbsp;{!! $cb($checklist->entry_via_permeable) !!} Permeable
                {!! $sep !!}
                {!! $cb($checklist->entry_asepsis_done) !!} <strong>Asepsia de sitio realizada</strong>
                {!! $sep !!}
                <strong>Control de seguridad:</strong><br>
                {!! $cb($checklist->entry_check_flebotech) !!} Flebotech<br>
                {!! $cb($checklist->entry_check_availability) !!} Disponibilidad sangre/hemoderivado<br>
                {!! $cb($checklist->entry_check_transport) !!} Traslado adecuado del producto<br>
                {!! $cb($checklist->entry_check_vitals) !!} Signos vitales previos
                {!! $sep !!}
                {!! $cb($checklist->entry_equipment_ok) !!} <strong>Equipo funciona correctamente</strong>
                {!! $sep !!}
                <strong>¿Tiene el paciente?</strong>
                {!! $sep !!}
                <strong>Alergias conocidas:</strong>
                {!! $cb($checklist->entry_allergies === 'no') !!} No
                &nbsp;{!! $cb($checklist->entry_allergies === 'yes') !!} Sí
                @if($checklist->entry_allergies === 'yes' && $checklist->entry_allergies_detail)
                    — <em>{{ $checklist->entry_allergies_detail }}</em>
                @endif
                {!! $sep !!}
                <strong>Reacciones previas:</strong><br>
                {!! $cb($checklist->entry_previous_reactions === 'no') !!} No
                &nbsp;{!! $cb($checklist->entry_previous_reactions === 'yes_doctor_aware') !!} Sí, médico enterado
                {!! $sep !!}
                <strong>Riesgo hemorragia</strong> (&gt;500ml adultos / &gt;7ml/kg niños):<br>
                {!! $cb($checklist->entry_bleeding_risk === 'no') !!} No
                &nbsp;{!! $cb($checklist->entry_bleeding_risk === 'yes_with_access') !!} Sí, con vías colocadas
                {!! $sep !!}
                <strong>Hemoderivados disponibles:</strong><br>
                {!! $cb($checklist->entry_blood_products_available === 'no') !!} No
                &nbsp;{!! $cb($checklist->entry_blood_products_available === 'yes_crossmatched') !!} Sí, con cruce previo

            </div>
        </td>

        {{-- ══════════════════════════ --}}
        {{-- CELDA 2: PAUSA             --}}
        {{-- ══════════════════════════ --}}
        <td style="width:33%;vertical-align:top;padding:0 3px;">
            <div style="background-color:#E91E63;color:white;padding:2px 5px;
                        font-weight:bold;font-size:8pt;text-align:center;">
                2. PAUSA
            </div>
            <div class="col-content" style="border:1px solid #333;border-top:none;padding:4px 6px;
                        font-size:6.5pt;line-height:1.5;">

                <strong>Se presenta por nombre y función:</strong><br>
                {!! $cb($checklist->pause_doctor_on_duty_present) !!} Médico de guardia<br>
                {!! $cb($checklist->pause_anesthesiologist_present) !!} Anestesiólogo<br>
                {!! $cb($checklist->pause_nurse_present) !!} Personal de Enfermería
                {!! $sep !!}
                <strong>Confirmación verbal e individual:</strong><br>
                {!! $cb($checklist->pause_identity_verified) !!} Identidad del paciente<br>
                {!! $cb($checklist->pause_indication_verified) !!} Indicación de la transfusión<br>
                {!! $cb($checklist->pause_access_verified) !!} Vía de acceso única y permeable<br>
                {!! $cb($checklist->pause_product_verified) !!} Producto a transfundir
                {!! $sep !!}
                <strong>Datos del producto:</strong><br>
                <strong>Grupo:</strong> {{ $checklist->product_group ?? '___' }}
                &nbsp;&nbsp;<strong>RH:</strong> {{ $checklist->product_rh_factor ?? '___' }}<br>
                <strong>FOLIO:</strong> {{ $checklist->product_folio ?? '___' }}
                &nbsp;&nbsp;<strong>Cantidad:</strong> {{ $checklist->product_quantity ?? '___' }}
                {!! $sep !!}
                <strong>Tipo de producto:</strong><br>
                <strong>Vol. total:</strong>
                {{ $checklist->product_volume_total !== null ? $fmt($checklist->product_volume_total) . ' ml' : '___' }}<br>
                {!! $cb($checklist->product_red_cells) !!}
                Conc. Eritrocitario{{ $checklist->product_red_cells_amount ? ' — ' . $fmt($checklist->product_red_cells_amount) . ' ml' : '' }}<br>
                {!! $cb($checklist->product_fresh_plasma) !!}
                Plasma fresco congelado{{ $checklist->product_fresh_plasma_amount ? ' — ' . $fmt($checklist->product_fresh_plasma_amount) . ' ml' : '' }}<br>
                {!! $cb($checklist->product_platelet_concentrate) !!}
                Conc. plaquetario{{ $checklist->product_platelet_concentrate_amount ? ' — ' . $fmt($checklist->product_platelet_concentrate_amount) . ' ml' : '' }}<br>
                {!! $cb($checklist->product_cryoprecipitate) !!}
                Crioprecipitado{{ $checklist->product_cryoprecipitate_amount ? ' — ' . $fmt($checklist->product_cryoprecipitate_amount) . ' ml' : '' }}<br>
                {!! $cb($checklist->product_factor_vii) !!}
                Factor VII{{ $checklist->product_factor_vii_amount ? ' — ' . $fmt($checklist->product_factor_vii_amount) . ' ml' : '' }}<br>
                {!! $cb($checklist->product_apheresis) !!}
                Aféresis{{ $checklist->product_apheresis_amount ? ' — ' . $fmt($checklist->product_apheresis_amount) . ' ml' : '' }}<br>
                @if(!empty($checklist->product_other))
                    Otro: {{ $checklist->product_other }}{{ $checklist->product_other_amount ? ' — ' . $fmt($checklist->product_other_amount) . ' ml' : '' }}<br>
                @endif
                {!! $sep !!}
                <strong>Signos vitales (pre-transfusión):</strong><br>
                FC: {{ $checklist->pause_vitals_fc ?? '___' }}
                &nbsp;TA: {{ $checklist->pause_vitals_ta ?? '___' }}<br>
                TEMP: {{ $checklist->pause_vitals_temp ?? '___' }}
                &nbsp;FR: {{ $checklist->pause_vitals_fr ?? '___' }}

            </div>
        </td>

        {{-- ══════════════════════════ --}}
        {{-- CELDA 3: DURANTE Y SALIDA  --}}
        {{-- ══════════════════════════ --}}
        <td style="width:34%;vertical-align:top;padding-left:3px;">
            <div style="background-color:#E91E63;color:white;padding:2px 5px;
                        font-weight:bold;font-size:8pt;text-align:center;">
                3. DURANTE Y SALIDA
            </div>
            <div class="col-content" style="position:relative;border:1px solid #333;border-top:none;
                        padding:4px 6px 165px 6px;
                        font-size:6.5pt;line-height:1.5;height:395px;">

                <strong>Durante la transfusión:</strong><br>
                {!! $cb($checklist->during_monitoring_done) !!} El responsable monitoriza al paciente<br>
                {!! $cb($checklist->during_vitals_monitored) !!} Signos vitales<br>
                {!! $cb($checklist->during_adverse_reactions_monitored) !!} Reacciones adversas<br>
                {!! $cb($checklist->during_duration_monitored) !!} Duración de la transfusión<br>
                {!! $cb($checklist->during_via_permeability_monitored) !!} Permeabilidad de la vía
                {!! $sep !!}
                <strong>Al terminar confirman:</strong><br>
                {!! $cb($checklist->exit_vitals_confirmed) !!} Signos vitales<br>
                {!! $cb($checklist->exit_logbook_filled) !!} Llenado de libreta de transfusión<br>
                {!! $cb($checklist->exit_bag_disposed) !!} Bolsa y equipo en contenedor RPBI
                {!! $sep !!}
                <strong>¿Ocurrieron eventos adversos?</strong><br>
                {!! $cb(!$checklist->adverse_events_occurred) !!} No
                &nbsp;{!! $cb($checklist->adverse_events_occurred) !!} Sí
                @if($checklist->adverse_events_occurred && $checklist->adverse_events_detail)
                    <br><em>¿Cuál? {{ $checklist->adverse_events_detail }}</em>
                @endif
                {!! $sep !!}
                <strong>¿Se registró el evento adverso?</strong><br>
                {!! $cb(!$checklist->adverse_events_registered) !!} No
                &nbsp;{!! $cb($checklist->adverse_events_registered) !!} Sí
                @if($checklist->adverse_events_registered && $checklist->adverse_events_register_location)
                    <br><em>¿Dónde? {{ $checklist->adverse_events_register_location }}</em>
                @endif

                {{-- Firmas apiladas verticalmente al fondo de la columna --}}
                <div style="position:absolute;bottom:4px;left:5px;right:5px;
                            font-size:6.5pt;line-height:1.3;">

                    <div style="border-top:1px solid #555;padding-top:2px;">
                        <strong>ANESTESIÓLOGO</strong>
                    </div>
                    <div style="border-bottom:1px solid #333;height:9px;margin-top:2px;">&nbsp;</div>
                    <div style="font-size:5.5pt;color:#666;margin-bottom:3px;">Nombre</div>
                    <div style="border-bottom:1px solid #333;height:9px;">&nbsp;</div>
                    <div style="font-size:5.5pt;color:#666;margin-bottom:5px;">Firma</div>

                    <div style="border-top:1px solid #555;padding-top:2px;">
                        <strong>MÉDICO DE GUARDIA</strong>
                    </div>
                    <div style="border-bottom:1px solid #333;height:9px;margin-top:2px;">&nbsp;</div>
                    <div style="font-size:5.5pt;color:#666;margin-bottom:3px;">Nombre</div>
                    <div style="border-bottom:1px solid #333;height:9px;">&nbsp;</div>
                    <div style="font-size:5.5pt;color:#666;margin-bottom:5px;">Firma</div>

                    <div style="border-top:1px solid #555;padding-top:2px;">
                        <strong>ENFERMERA</strong>
                    </div>
                    <div style="border-bottom:1px solid #333;height:9px;margin-top:2px;">&nbsp;</div>
                    <div style="font-size:5.5pt;color:#666;margin-bottom:3px;">Nombre</div>
                    <div style="border-bottom:1px solid #333;height:9px;">&nbsp;</div>
                    <div style="font-size:5.5pt;color:#666;">Firma</div>

                </div>

            </div>
        </td>

    </tr>
</table>

@endsection