@extends('pdfs.layouts.base')

@section('document-title', 'HOJAS DE ENFERMERÍA')

@section('content')
    <style>
        @page { margin: 100px 25px 60px 25px; }

        /* ── Estilos compartidos de las Hojas de Enfermería ── */
        .chapter-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 5px;
            margin: 0 0 10px;
            background: #FCE4EC;
            border: 1px solid #C2185B;
            color: #880E4F;
        }

        .subsection-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #E3F2FD;
            border-left: 4px solid #1976D2;
            padding: 3px 6px;
            margin: 12px 0 5px;
        }

        .day-header {
            font-size: 10.5px;
            font-weight: bold;
            background: #f0f0f0;
            border: 1px solid #bbb;
            padding: 4px 6px;
            margin: 10px 0 5px;
        }

        .day-header .sub {
            font-weight: normal;
            font-size: 8.5px;
            color: #555;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.grid th,
        table.grid td {
            border: 1px solid #bbb;
            padding: 3px 4px;
            font-size: 8.5px;
            vertical-align: top;
        }

        table.grid th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }

        table.grid td.num { text-align: right; }
        table.grid td.center, table.grid th.center { text-align: center; }

        /* Tabla densa del balance de líquidos (16 columnas en carta vertical) */
        table.fb-table { table-layout: fixed; }
        table.fb-table th,
        table.fb-table td {
            padding: 1px 2px;
            font-size: 6.5px;
            word-wrap: break-word;
            overflow: hidden;
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .kv td {
            border: 1px solid #ccc;
            padding: 3px 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .kv td.label {
            background: #f7f7f7;
            font-weight: bold;
            width: 130px;
            text-transform: uppercase;
            font-size: 8px;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            color: #fff;
        }
        .badge-green  { background: #2E7D32; }
        .badge-yellow { background: #F9A825; color: #000; }
        .badge-red    { background: #C62828; }
        .badge-gray   { background: #757575; }
        .badge-blue   { background: #1565C0; }

        .muted { color: #888; }
        .empty-note { font-style: italic; color: #888; font-size: 9px; margin: 4px 0 10px; }

        .summary-box {
            border: 1px solid #C2185B;
            background: #FCE4EC;
            padding: 2px 6px;
            margin-bottom: 6px;
            font-size: 8.5px;
            color: #880E4F;
        }
        .summary-box .val { font-weight: bold; }

        .gen-note {
            margin-top: 14px;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }
    </style>

    {{-- Portada: datos generales del paciente y la estancia --}}
    @include('pdfs.nursing-sheets.partials.cover')

    {{-- Hoja 1 — Registros clínicos y signos vitales --}}
    <div style="page-break-before: always;"></div>
    @include('pdfs.nursing-sheets.partials.section-1-vitals', [
        'vitalSignReadings' => $vitalSignReadings,
        'shiftSummaries'    => $shiftSummaries,
        'admissionDate'     => $admissionDate,
        'endDate'           => $endDate,
    ])

    {{-- Hoja 2 — Medicamentos y notas de enfermería --}}
    <div style="page-break-before: always;"></div>
    @include('pdfs.nursing-sheets.partials.section-2-medications-notes', [
        'medicationOrders'    => $stay->medicationOrders,
        'nursingEntriesByDay' => $nursingEntriesByDay,
    ])

    {{-- Hojas 3-4 — Balance de líquidos (solo si hubo orden) --}}
    @if($hasFluidBalance)
        <div style="page-break-before: always;"></div>
        @include('pdfs.nursing-sheets.partials.section-3-fluid-balance')
    @endif
@endsection
