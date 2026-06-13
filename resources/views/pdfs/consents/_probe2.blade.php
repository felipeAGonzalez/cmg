<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 110px 40px 70px 40px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; }
        header { position: fixed; top: -90px; left: 0; right: 0; height: 80px; border-bottom: 2px solid #1976D2; }
        footer { position: fixed; bottom: -50px; left: 0; right: 0; height: 40px; border-top: 1px solid #ccc; font-size: 8px; color: #777; }
        /* NOTE: footer WITHOUT floats */
    </style>
</head>
<body>
    <header><div style="text-align:center;">CENTRO MÉDICO GUADALUPANO</div></header>
    <footer>
        <table style="width:100%; border:none;"><tr>
          <td style="border:none; text-align:left;">Centro Médico Guadalupano</td>
          <td style="border:none; text-align:right;">Expediente clínico electrónico</td>
        </tr></table>
    </footer>
    <main>
        @php $L = 'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor in reprehenderit.'; @endphp
        <div style="border:1px solid blue;">NOFLOAT footer, plain div: {{ $L }}</div>
        <table class="data-table" style="width:100%; border-collapse:collapse;">
          <tr><td style="border:1px solid green;">NOFLOAT data-table td: {{ $L }}</td></tr>
        </table>
    </main>
</body>
</html>
