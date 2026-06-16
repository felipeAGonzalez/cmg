@extends('pdfs.layouts.base')

@section('document-title', '')

@section('content')
@php
    $g = strtoupper($patient->gender ?? '');
    $isMale = $g === 'M' || $g === 'MASCULINO';
    $isFemale = $g === 'F' || $g === 'FEMENINO';
    $patientFullName = $patient->fullName();
    $patientBirth = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') : '';
    $patientAge = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age . ' AÑOS' : '';
@endphp

<table class="data-table" style="width:100%; font-size:9px;">
    {{-- ===== Encabezado institucional rosa CMG (filas de la data-table; una
         tabla suelta aparte rompe la paginación al combinarse con esta) ===== --}}
    <tr><td style="border:none; background-color:#E91E63; color:white; padding:3px 9px; font-weight:bold; font-size:12px; text-align:center;">
        HOJA DE AUTORIZACIÓN DE INTERVENCIÓN QUIRÚRGICA <br> CARTA DE CONSENTIMIENTO AUTORIZADO BAJO INFORMACIÓN
    </td></tr>
    <tr><td style="border:none; text-align:right; font-size:9px; padding-top:4px;">
        <strong>FOLIO:</strong> {{ $data['folio'] ?? '________' }}
    </td></tr>

    {{-- Datos institucionales fijos (una fila por renglón; sin <br>) --}}
    <tr><td style="border:none; text-align:right; font-size:8px; color:#333;">Privada Solar #3 Zona Centro Acámbaro, GTO.</td></tr>
    <tr><td style="border:none; text-align:right; font-size:8px; color:#333; padding-bottom:0px;">C.P. 38600 Teléfonos 01 (417) 172 04 30 y 172 81 30</td></tr>

    {{-- Texto legal de fundamento (prosa continua) --}}
    <tr><td style="border:none; text-align:justify; font-size:8px; line-height:1.4; padding-bottom:8px;">
        Con fundamento en la Ley General de Salud, Artículo 77 Bis, Reglamento de la Ley General de Salud en
        materia de prestación de servicios de atención médica, Artículos 80, 81, 82, 83 y a la Norma Oficial
        Mexicana NOM-004-SSA1-2012 del expediente clínico fracciones 10.1.1.1.; a la 10.1.1.4. y en cumplimiento
        a la ley de transparencia y acceso de información pública y gubernamental (ifai).
    </td></tr>

    {{-- Datos del paciente, responsable, médico y diagnósticos como PROSA
         continua: los valores en negrita dentro de la oración (sin filas tipo
         ficha) para que se lea como un documento redactado. Ver memoria
         dompdf-pagination-pitfall: en párrafos justificados se usa negrita,
         no border-bottom (deja subrayados fantasma). --}}
    @php
        // Valor en negrita dentro de la prosa; relleno con guiones si está vacío.
        $b = fn ($t) => trim((string) $t) !== '' ? '<strong>' . e($t) . '</strong>' : '________';
        $sexLabel = $isMale ? 'MASCULINO' : ($isFemale ? 'FEMENINO' : '');
        $diagnoses = array_values(array_filter(array_map('trim', $data['diagnoses'] ?? []), fn ($d) => $d !== ''));
        $benefits  = array_values(array_filter(array_map('trim', $data['benefits'] ?? []), fn ($d) => $d !== ''));
        $risks     = array_values(array_filter(array_map('trim', $data['risks'] ?? []), fn ($d) => $d !== ''));
        // Lista enumerada en línea: "1) <b>x</b>; 2) <b>y</b>".
        $numbered = function (array $items) {
            if (empty($items)) {
                return '________';
            }
            $out = [];
            foreach ($items as $i => $it) {
                $out[] = ($i + 1) . ') <strong>' . e($it) . '</strong>';
            }
            return implode('; ', $out);
        };
    @endphp

    <tr><td style="border:none; text-align:justify; line-height:1.6; padding-bottom:6px;">
        POR ESTE CONDUCTO, EL QUE SUSCRIBE, C. {!! $b($patientFullName) !!}, SEXO {!! $b($sexLabel) !!},
        EDAD {!! $b($patientAge) !!}, FECHA DE NACIMIENTO {!! $b($patientBirth) !!},
        TELÉFONO {!! $b($data['patient_phone'] ?? '') !!}, Y ACOMPAÑADO POR {!! $b($data['responsible_name'] ?? '') !!}
        (PARENTESCO: {!! $b($data['responsible_relationship'] ?? '') !!}@if(!empty($data['responsible_address'])), DOMICILIO: {!! $b($data['responsible_address']) !!}@endif);
        HAGO CONSTAR QUE EL DR.(A) {!! $b($data['doctor_name'] ?? '') !!}, CON CÉDULA PROFESIONAL
        {!! $b($data['doctor_cedula'] ?? '') !!}, ME MENCIONA QUE MI(S) DIAGNÓSTICO(S) PROBABLE(S) O
        CONFIRMADO(S) ES/SON: {!! $numbered($diagnoses) !!}.
    </td></tr>

    <tr><td style="border:none; text-align:justify; line-height:1.6; padding-bottom:6px;">
        POR LO CUAL ES NECESARIO REALIZAR EL/LOS SIGUIENTE(S) PROCEDIMIENTO(S): QUIRÚRGICO:
        {!! $b($data['surgical_procedure'] ?? '') !!}; INVASIVO: {!! $b($data['invasive_procedure'] ?? '') !!}.
    </td></tr>

    {{-- Declaración legal + beneficios y riesgos, todo en prosa continua --}}
    <tr><td style="border:none; text-align:justify; line-height:1.5; padding-bottom:6px;">
        DECLARO QUE EL MÉDICO QUE ME ATENDIÓ, DESPUÉS DE HABER REALIZADO TODAS LAS OBSERVACIONES, SE ME EXPLICÓ A MI
        TOTAL ENTENDER Y SABER LA ENFERMEDAD, ENUMERANDO, EXPLICANDO Y SABIENDO QUE ESTA LISTA INCLUYE PERO NO
        SE LIMITA A LOS SIGUIENTES: BENEFICIOS: {!! $numbered($benefits) !!}. RIESGOS O COMPLICACIONES:
        {!! $numbered($risks) !!}.
    </td></tr>

    {{-- Texto fijo de alternativas (prosa continua) --}}
    <tr><td style="border:none; text-align:justify; line-height:1.5; padding-bottom:8px;">
        MENCIONADAS COMO CONSECUENCIA DE SU CURSO NATURAL Y/O POR LOS PROCEDIMIENTOS MÉDICOS Y/O QUIRÚRGICOS QUE SE
        ME PRACTIQUEN; Y POR LO TANTO, DESPUÉS DE HABER COMPRENDIDO LO ARRIBA MENCIONADO, SE ME HAN COMUNICADO LAS
        ALTERNATIVAS EXISTENTES Y DISPONIBLES: {!! $b($data['alternatives'] ?? '') !!}.
    </td></tr>

    {{-- Encabezado rosa del consentimiento --}}
    <tr><td style="border:none; background-color:#E91E63; color:white; padding:5px 10px; font-weight:bold; text-align:center; font-size:11px;">
        CONSENTIMIENTO AUTORIZADO BAJO INFORMACIÓN
    </td></tr>

    {{-- Declaración principal (prosa continua con datos) --}}
    <tr><td style="border:none; text-align:justify; line-height:1.5; padding-top:6px;">
        <strong>YO, {{ $patientFullName }}</strong>
        manifiesto bajo mi libre voluntad y en uso de mis facultades mi completa satisfacción autorizo los
        procedimientos. Manifiesto mi libre voluntad para autorizar los procedimientos diagnósticos, terapéuticos
        y quirúrgicos que se me indiquen o apliquen después de haber recibido y entendido la información suficiente,
        clara, oportuna y veraz sobre mi enfermedad y estado actual, que comprendo el alcance del tratamiento y/o
        procedimientos y que con esto conllevo los beneficios, riesgos, complicaciones y secuelas inherentes. Se me
        han comunicado las alternativas existentes y disponibles, y se que cuento con el derecho a cambiar mi
        decisión en cualquier momento antes del procedimiento o intervención. Me comprometo a proporcionar
        información completa y veraz, así como seguir las indicaciones médicas con el propósito de que mi atención
        sea adecuada. Del mismo modo asigno a: <strong>{{ $data['designated_person'] ?? '________________' }}</strong>
        para recibir información del estado de salud, diagnóstico, tratamiento y pronóstico en caso de que por mi
        condición no pueda tomar decisiones.
    </td></tr>
    <tr><td style="border:none; text-align:justify; line-height:1.5; padding-top:6px;">
        Otorgo mi autorización al Personal de Salud paramédicos y médicos de la unidad médica centro medico
        guadalupano, de la cd. de Acámbaro, Gto. para la atención de contingencias y urgencias derivadas del
        acto médico señalado, atendiendo al principio de libertad prescriptiva.
    </td></tr>
    <tr><td style="border:none; text-align:justify; line-height:1.5; padding-top:6px; padding-bottom:6px;">
        HE LEÍDO Y MANIFIESTO QUE ENTIENDO EL CONTENIDO TOTAL DE ESTE INSTRUMENTO MEDICO-JURÍDICO Y ESTOY
        SATISFECHO CON LA INFORMACIÓN QUE SE ME HA DADO Y FIRMO MI <strong>CONSENTIMIENTO AUTORIZADO BAJO
        INFORMACIÓN</strong>, EN LA CD. DE <strong>{{ strtoupper($data['city'] ?? 'ACÁMBARO, GTO.') }}</strong>,
        A LOS <strong>{{ $data['signed_day'] ?? '____' }}</strong> DÍAS DEL MES DE
        <strong>{{ strtoupper($data['signed_month'] ?? '____________') }}</strong>
        DEL AÑO 20<strong>{{ substr((string)($data['signed_year'] ?? '____'), -2) }}</strong>;
        SIENDO LAS <strong>{{ $data['signed_time'] ?? '____' }}</strong> HRS.
    </td></tr>

    {{-- Firmas: tabla ANIDADA (modelo de columnas propio 50/50, aislado del
         justificado de la tabla principal de una sola columna). Una fila por
         renglón (ver memoria: <br> en celdas al 50% colapsa la fila). --}}
    <tr><td style="border:none; padding-top:6px;">
        @php $fc = 'border:none; width:50%; text-align:center;'; @endphp
        <table style="width:100%; border-collapse:collapse; font-size:9px;">
            <tr>
                <td style="{{ $fc }} padding-top:34px;">_____________________________</td>
                <td style="{{ $fc }} padding-top:34px;">_____________________________</td>
            </tr>
            <tr>
                <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA DEL PACIENTE O REPRESENTANTE LEGAL</strong></td>
                <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA DEL MÉDICO ( CED. PROF. ), SSA</strong></td>
            </tr>
            <tr>
                <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['responsible_name'] ?? '' }}</td>
                <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['doctor_name'] ?? '' }}@if(!empty($data['doctor_cedula'])) — Céd. {{ $data['doctor_cedula'] }}@endif</td>
            </tr>
            <tr>
                <td style="{{ $fc }} padding-top:40px;">_____________________________</td>
                <td style="{{ $fc }} padding-top:40px;">_____________________________</td>
            </tr>
            <tr>
                <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA DE TESTIGO</strong></td>
                <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA DEL TESTIGO</strong></td>
            </tr>
            <tr>
                <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['witness_1_name'] ?? '' }}</td>
                <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['witness_2_name'] ?? '' }}</td>
            </tr>
        </table>
    </td></tr>
</table>
@endsection
