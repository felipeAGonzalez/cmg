<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('document-title', 'Documento') — Centro Médico Guadalupano</title>
    <style>
        @page { margin: 110px 40px 70px 40px; }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
        }

        /* Encabezado fijo en cada página */
        header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 2px solid #1976D2;
            padding-bottom: 6px;
        }

        header table { width: 100%; border-collapse: collapse; }
        header td { border: none; padding: 0; vertical-align: middle; }
        header td.logo-cell { width: 70px; text-align: left; }
        header td.brand-cell { text-align: center; }

        header .logo {
            max-height: 60px;
            max-width: 65px;
        }

        header .name {
            font-size: 15px;
            font-weight: bold;
            color: #1976D2;
            letter-spacing: .5px;
        }

        header .subtitle {
            font-size: 9px;
            color: #555;
        }

        /* Pie fijo con número de página */
        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid #ccc;
            padding-top: 4px;
            font-size: 8px;
            color: #777;
        }

        /* NO usar float aquí: en DomPDF los float del footer fijo se filtran al
           flujo del <main> y empujan las líneas de texto a la mitad derecha de
           la página. Se usa una tabla para alinear izquierda/derecha. */
        footer table { width: 100%; border-collapse: collapse; }
        footer td { border: none; padding: 0; }
        footer td.left  { text-align: left; }
        footer td.right { text-align: right; }

        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 10px;
            padding: 4px;
            background: #eef4fb;
            border: 1px solid #1976D2;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f0f0f0;
            border-left: 4px solid #1976D2;
            padding: 3px 6px;
            margin: 10px 0 4px;
        }

        .text-block {
            border: 1px solid #000;
            padding: 6px;
            min-height: 28px;
            margin-bottom: 6px;
            white-space: pre-wrap;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 9.5px;
        }

        .field-label {
            font-weight: bold;
            background: #f7f7f7;
            text-transform: uppercase;
            font-size: 8.5px;
        }
    </style>
</head>
<body>
    <header>
        @php
            $logoPath = public_path('logos/CMG.png');
            $logoData = is_file($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : null;
        @endphp
        <table>
            <tr>
                <td class="logo-cell">
                    @if($logoData)
                        <img src="{{ $logoData }}" class="logo" alt="CMG">
                    @endif
                </td>
                <td class="brand-cell">
                    <div class="name">CENTRO MÉDICO GUADALUPANO</div>
                    <div class="subtitle">Expediente clínico</div>
                </td>
                <td style="width:70px;"></td>
            </tr>
        </table>
    </header>

    <footer>
        <table>
            <tr>
                <td class="left">
                    Centro Médico Guadalupano — Documento generado el {{ now()->format('d/m/Y H:i') }}
                </td>
                <td class="right">Expediente clínico electrónico</td>
            </tr>
        </table>
    </footer>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $pdf->page_text($pdf->get_width() / 2 - $width / 2, $pdf->get_height() - 28, $text, $font, $size, [0.46, 0.46, 0.46]);
        }
    </script>

    <main>
        <div class="doc-title">@yield('document-title', 'Documento')</div>
        @yield('content')
    </main>
</body>
</html>
