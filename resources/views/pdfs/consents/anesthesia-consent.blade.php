@extends('pdfs.layouts.base')

@section('document-title', '')

@section('content')
@php
    $g = strtoupper($patient->gender ?? '');
    $isMale = $g === 'M' || $g === 'MASCULINO';
    $isFemale = $g === 'F' || $g === 'FEMENINO';
    $patientFullName = $patient->fullName();
    $patientBirth = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') : '';
    $patientAge = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age : '';
    // Línea de relleno tipo "ficha": SOLO en líneas alineadas a la izquierda.
    // Dentro de párrafos justificados deja fragmentos a la izquierda, así que
    // ahí se usan valores en negrita (ver memoria dompdf-pagination-pitfall).
    $fill = 'border-bottom:1px solid #333; padding:0 4px;';
    $box  = 'border:1px solid #333; padding:0 4px;';
@endphp

<table class="data-table" style="width:100%; font-size:9px;">
    {{-- ===== Encabezado institucional rosa CMG ===== --}}
    <tr><td colspan="2" style="border:none; background-color:#E91E63; color:white; padding:6px 12px; font-weight:bold; font-size:12px; text-align:center;">
        CARTA DE CONSENTIMIENTO INFORMADO PARA LA APLICACIÓN DE ANESTESIA
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:right; font-size:8px; color:#333; padding-top:4px;">Dr. Jesús Rogelio Páramo Figueroa</td></tr>
    <tr><td colspan="2" style="border:none; text-align:right; font-size:8px; color:#333;">Privada Solar #3 Zona Centro Acámbaro, GTO.</td></tr>
    <tr><td colspan="2" style="border:none; text-align:right; font-size:8px; color:#333; padding-bottom:6px;">C.P. 38600 Teléfonos 01 (417) 172 04 30 y 172 81 30</td></tr>

    {{-- Datos del paciente y representante (ficha, alineado a la izquierda) --}}
    <tr><td colspan="2" style="border:none; text-align:left; line-height:1.9;">
        <strong>NOMBRE DEL PACIENTE:</strong> <span style="{{ $fill }}">{{ $patientFullName }}</span>
        &nbsp;&nbsp;<strong>EDAD:</strong> <span style="{{ $fill }}">{{ $patientAge }}</span>
        &nbsp;&nbsp;<strong>SEXO:</strong>
        <span style="{{ $box }}">{!! $isMale ? 'X' : '&nbsp;' !!}</span> M
        <span style="{{ $box }}">{!! $isFemale ? 'X' : '&nbsp;' !!}</span> F
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:left; line-height:1.9;">
        <strong>FECHA NACIMIENTO:</strong> <span style="{{ $fill }}">{{ $patientBirth }}</span>
        &nbsp;&nbsp;<strong>TEL.:</strong> <span style="{{ $fill }}">{{ $data['patient_phone'] ?? '' }}</span>
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:left; line-height:1.9;">
        <strong>NOMBRE DEL REPRESENTANTE LEGAL:</strong> <span style="{{ $fill }}">{{ $data['responsible_name'] ?? '' }}</span>
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:left; line-height:1.9; padding-bottom:6px;">
        <strong>RELACIÓN CON EL PACIENTE:</strong> <span style="{{ $fill }}">{{ $data['responsible_relationship'] ?? '' }}</span>
        &nbsp;&nbsp;<strong>DOMICILIO:</strong> <span style="{{ $fill }}">{{ $data['responsible_address'] ?? '' }}</span>
    </td></tr>

    {{-- Apertura de la declaración --}}
    <tr><td colspan="2" style="border:none; text-align:left; line-height:1.7;">
        <strong>Yo {{ $patientFullName }}</strong>, en pleno uso de mis facultades mentales y en mi calidad de
        paciente, o representante legal de este:
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:center; font-weight:bold; font-size:10px; padding-bottom:4px;">
        DECLARO EN FORMA LIBRE Y VOLUNTARIA LO SIGUIENTE:
    </td></tr>

    {{-- Las 9 cláusulas (prosa continua justificada; valores en negrita) --}}
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>1.</strong> En base a mi derecho inalienable de elegir a mi médico, acepto al Dr.(a)
        <strong>{{ $data['anesthesiologist_name'] ?? '________________' }}</strong> como mi Médico Anestesiólogo,
        quién está avalado por el Colegio de Anestesiólogos de por la Federación Mexicana de Colegios de
        Anestesiología, A.C., y debidamente autorizado para ejercer la Anestesiología por la Oficina Estatal de
        Profesiones de Gobierno del Estado de <strong>{{ $data['anesthesiologist_state'] ?? 'Guanajuato' }}</strong>.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>2.</strong> Entiendo que las complicaciones, aunque poco probables, son posibles, y pueden ser
        desde leves, tales como: pérdida o daño de una pieza dental, dolor de espalda, o en el sitio de punción,
        dolor de cabeza, alteraciones asociadas con la posición quirúrgica, dificultad transitoria para orinar,
        molestias oculares o de garganta, heridas en boca y tos; hasta severas tales como aspiración del
        contenido gástrico, descompensación de mis enfermedades crónicas, alteraciones cardiacas, renales, de la
        presión arterial, complicaciones pulmonares, reacciones medicamentosas, transfusionales, lesiones
        nerviosas o de médula espinal. Todas ellas pudieran causar secuelas permanentes e incluso llevar al
        fallecimiento. El beneficio que obtendré con la aplicación de la anestesia es que se pueda llevar a cabo
        el procedimiento diagnostico y/o quirúrgico llamado
        <strong>{{ $data['procedure_name'] ?? '________________' }}</strong> para intentar mejorar mi estado de salud.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>3.</strong> Entiendo también que todo acto médico implica una serie de riesgos que pueden deberse
        a mi estado de salud, alteraciones congénitas o anatómicas que padezca, mis antecedentes de enfermedades,
        tratamientos actuales y previos, a la técnica anestésica o quirúrgica, al equipo medico utilizado y/o a la
        enfermedad que condiciona el procedimiento medico o quirúrgico al que he decidido someterme.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>4.</strong> Estoy conciente de que puedo requerir de tratamientos complementarios que aumenten mi
        estancia hospitalaria con la participación de otros servicios o unidades médicas, con el incremento
        consecuente de los costos.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>5.</strong> El Médico Anestesiólogo ha respondido mis dudas y me ha explicado en lenguaje claro y
        sencillo las alternativas anestésicas posibles y <strong>ACEPTO</strong> anestesia tipo
        <strong>{{ $data['anesthesia_type'] ?? '________________' }}</strong>, que es de carácter electivo
        <strong>[{{ ($data['anesthesia_character'] ?? '') === 'elective' ? 'X' : ' ' }}]</strong>
        urgente
        <strong>[{{ ($data['anesthesia_character'] ?? '') === 'urgent' ? 'X' : ' ' }}]</strong>
        y he entendido los posibles riesgos y complicaciones de esta técnica anestésica.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>6.</strong> Se me ha explicado que en mi atención pudieran intervenir médicos en entrenamiento de
        la especialidad de Anestesiología, pero siempre bajo la vigilancia y supervisión de mi Médico
        Anestesiólogo. (SOLO PARA HOSPITALES CON RESIDENTES)
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>7.</strong> En mi presencia han sido llenados o cancelados todos los espacios en blanco que se
        presentan en este documento.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0;">
        <strong>8.</strong> Se me ha informado que de no existir este documento en mi expediente, no se podrá
        llevar a cabo el procedimiento planeado.
    </td></tr>
    <tr><td colspan="2" style="border:none; text-align:justify; font-size:8.5px; line-height:1.4; padding:2px 0 6px;">
        <strong>9.</strong> En virtud de estar aclaradas todas mis dudas, <strong>DOY MI CONSENTIMIENTO</strong>
        para que mi persona o representado, pueda ser anesthesiado con los riesgos inherentes al procedimiento y
        autorizo al anestesiólogo para que de acuerdo a su criterio, cambie la técnica anestésica intentando con
        ello resolver cualquier situación que se presente durante el acto anestésico-quirúrgico o de acuerdo a mis
        condiciones físicas y/o emocionales.
    </td></tr>

    {{-- Firmas principales (una fila por renglón) --}}
    @php $fc = 'border:none; width:50%; text-align:center;'; @endphp
    <tr>
        <td style="{{ $fc }} padding-top:30px;">_____________________________</td>
        <td style="{{ $fc }} padding-top:30px;">_____________________________</td>
    </tr>
    <tr>
        <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA DEL MÉDICO ANESTESIÓLOGO</strong></td>
        <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA DEL PACIENTE O REPRESENTANTE LEGAL</strong></td>
    </tr>
    <tr>
        <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['anesthesiologist_name'] ?? '' }}</td>
        <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['responsible_name'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="{{ $fc }} padding-top:36px;">_____________________________</td>
        <td style="{{ $fc }} padding-top:36px;">_____________________________</td>
    </tr>
    <tr>
        <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA TESTIGO</strong></td>
        <td style="{{ $fc }}"><strong>NOMBRE Y FIRMA TESTIGO</strong></td>
    </tr>
    <tr>
        <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['witness_1_name'] ?? '' }}</td>
        <td style="{{ $fc }} font-size:8px; color:#555;">{{ $data['witness_2_name'] ?? '' }}</td>
    </tr>

    {{-- Sección Negación (condicional) --}}
    @if(!empty($data['negation']['applies']))
        <tr><td colspan="2" style="border:none; background-color:#E91E63; color:white; padding:5px 10px; font-weight:bold; text-align:center; font-size:10px;">
            NEGACIÓN DEL CONSENTIMIENTO INFORMADO
        </td></tr>
        <tr><td colspan="2" style="border:none; text-align:justify; line-height:1.5; padding-top:6px;">
            Por la presente, <strong>NIEGO</strong> el consentimiento para que sean practicados en mi o en mi
            representado el manejo de la técnica anestésica y lo que derive de ella, conciente de que he sido
            informado de las consecuencias que resulten de esta negativa.
        </td></tr>
        <tr><td colspan="2" style="border:none; text-align:center; padding-top:30px;">_____________________________</td></tr>
        <tr><td colspan="2" style="border:none; text-align:center;"><strong>NOMBRE Y FIRMA DEL PACIENTE O REPRESENTANTE LEGAL</strong></td></tr>
    @endif

    {{-- Sección Revocación (condicional) --}}
    @if(!empty($data['revocation']['applies']))
        <tr><td colspan="2" style="border:none; background-color:#E91E63; color:white; padding:5px 10px; font-weight:bold; text-align:center; font-size:10px;">
            REVOCACIÓN DEL CONSENTIMIENTO
        </td></tr>
        <tr><td colspan="2" style="border:none; text-align:justify; line-height:1.5; padding-top:6px;">
            Por la presente, <strong>REVOCO</strong> el consentimiento otorgado en fecha
            <strong>{{ !empty($data['revocation']['original_consent_date']) ? \Carbon\Carbon::parse($data['revocation']['original_consent_date'])->format('d/m/Y') : '____________' }}</strong>
            y es mi deseo no proseguir el manejo anestésico que se indica en mi o en mi representado a partir de
            esta fecha
            <strong>{{ !empty($data['revocation']['revocation_date']) ? \Carbon\Carbon::parse($data['revocation']['revocation_date'])->format('d/m/Y') : '____________' }}</strong>,
            relevando de toda responsabilidad al anestesiólogo, toda vez que he entendido los alcances que conlleva
            esta revocación.
        </td></tr>
        <tr><td colspan="2" style="border:none; text-align:center; padding-top:30px;">_____________________________</td></tr>
        <tr><td colspan="2" style="border:none; text-align:center;"><strong>NOMBRE Y FIRMA DEL PACIENTE O REPRESENTANTE LEGAL</strong></td></tr>
    @endif
</table>
@endsection
