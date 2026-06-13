<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class VitalSignsChartGenerator
{
    /**
     * Genera la gráfica de signos vitales de la estancia como imagen PNG.
     *
     * La imagen se construye en memoria con GD y se devuelve como data URI
     * base64, lista para embeber en el PDF (<img src="data:image/png;base64,...">).
     * Nunca se escribe a disco.
     *
     * @param  Collection  $readings       Lecturas ordenadas por recorded_at.
     * @param  Carbon       $admissionDate  Inicio del rango (ingreso).
     * @param  Carbon       $endDate        Fin del rango (egreso o ahora).
     * @return string|null  Data URI base64, o null si no hay datos o GD no está disponible.
     */
    public static function generate(
        Collection $readings,
        Carbon $admissionDate,
        Carbon $endDate
    ): ?string {
        if (! extension_loaded('gd') || $readings->isEmpty()) {
            return null;
        }

        $width = 1200;
        $height = 480;
        $marginLeft = 95;
        $marginRight = 45;
        $marginTop = 60;
        $marginBottom = 70;
        $chartWidth = $width - $marginLeft - $marginRight;
        $chartHeight = $height - $marginTop - $marginBottom;

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return null;
        }

        // Colores
        $bgWhite    = imagecolorallocate($image, 255, 255, 255);
        $bgFaint    = imagecolorallocate($image, 250, 250, 250);
        $gridLight  = imagecolorallocate($image, 224, 224, 224);
        $gridBorder = imagecolorallocate($image, 153, 153, 153);
        $textDark   = imagecolorallocate($image, 51, 51, 51);
        $textMuted  = imagecolorallocate($image, 102, 102, 102);
        $colorHr    = imagecolorallocate($image, 211, 47, 47);   // F.C. rojo
        $colorRr    = imagecolorallocate($image, 46, 125, 50);   // F.R. verde

        // Fondo
        imagefilledrectangle($image, 0, 0, $width, $height, $bgWhite);
        imagefilledrectangle($image, $marginLeft, $marginTop, $width - $marginRight, $height - $marginBottom, $bgFaint);

        // Helpers de conversión
        $totalMinutes = max(1, $admissionDate->diffInMinutes($endDate));
        $timeToX = function (Carbon $carbon) use ($admissionDate, $totalMinutes, $marginLeft, $chartWidth) {
            $offset = $admissionDate->diffInMinutes($carbon);
            $offset = max(0, min($totalMinutes, $offset));
            return (int) round($marginLeft + ($offset / $totalMinutes) * $chartWidth);
        };

        $valueToY = function ($value, $min, $max) use ($marginTop, $chartHeight) {
            if ($value === null || $value === '' || $max == $min) {
                return null;
            }
            $normalized = max(0, min(1, ((float) $value - $min) / ($max - $min)));
            return (int) round($marginTop + $chartHeight * (1 - $normalized));
        };

        // Cuadrícula horizontal
        for ($i = 0; $i <= 10; $i++) {
            $y = $marginTop + (int) round($chartHeight * $i / 10);
            imageline($image, $marginLeft, $y, $width - $marginRight, $y, $gridLight);
        }

        // Eje Y: escala de referencia (F.C.)
        $axisFont = 4;
        $axisH = imagefontheight($axisFont);
        for ($v = 50; $v <= 170; $v += 20) {
            $y = $valueToY($v, 50, 170);
            $label = (string) $v;
            $lw = imagefontwidth($axisFont) * strlen($label);
            imagestring($image, $axisFont, $marginLeft - $lw - 10, $y - (int) round($axisH / 2), $label, $textMuted);
        }
        imagestring($image, $axisFont, 8, (int) round($marginTop + $chartHeight / 2 - $axisH / 2), 'F.C.', $textDark);

        // Eje X: granularidad adaptativa según duración
        $durationDays = $admissionDate->diffInDays($endDate);
        $ticks = [];
        if ($durationDays < 2) {
            $cursor = $admissionDate->copy()->startOfHour();
            while ($cursor->lessThanOrEqualTo($endDate)) {
                if ($cursor->hour % 4 === 0 && $cursor->greaterThanOrEqualTo($admissionDate)) {
                    $ticks[] = ['carbon' => $cursor->copy(), 'label' => $cursor->format('d/m H:i')];
                }
                $cursor->addHour();
            }
        } else {
            $stepDays = $durationDays <= 7 ? 1 : 2;
            $cursor = $admissionDate->copy()->startOfDay();
            while ($cursor->lessThanOrEqualTo($endDate)) {
                if ($cursor->greaterThanOrEqualTo($admissionDate)) {
                    $ticks[] = ['carbon' => $cursor->copy(), 'label' => $cursor->format('d/m')];
                }
                $cursor->addDays($stepDays);
            }
        }

        foreach ($ticks as $tick) {
            $x = $timeToX($tick['carbon']);
            imageline($image, $x, $marginTop, $x, $height - $marginBottom, $gridLight);
            $labelWidth = imagefontwidth(3) * strlen($tick['label']);
            imagestring($image, 3, $x - (int) round($labelWidth / 2), $height - $marginBottom + 8, $tick['label'], $textMuted);
        }

        // Marco del área de la gráfica
        imagerectangle($image, $marginLeft, $marginTop, $width - $marginRight, $height - $marginBottom, $gridBorder);

        // Dibuja una serie (línea conectada + puntos)
        $drawLine = function ($field, $min, $max, $color) use ($image, $readings, $timeToX, $valueToY) {
            $prevX = null;
            $prevY = null;
            foreach ($readings as $r) {
                if ($r->$field === null || $r->$field === '') {
                    continue;
                }
                $y = $valueToY($r->$field, $min, $max);
                if ($y === null) {
                    continue;
                }
                $x = $timeToX(Carbon::parse($r->recorded_at));

                if ($prevX !== null) {
                    // Trazo grueso: 5 líneas paralelas.
                    for ($d = -2; $d <= 2; $d++) {
                        imageline($image, $prevX, $prevY + $d, $x, $y + $d, $color);
                    }
                }

                imagefilledellipse($image, $x, $y, 9, 9, $color);

                $prevX = $x;
                $prevY = $y;
            }
        };

        $drawLine('heart_rate', 50, 170, $colorHr);
        $drawLine('respiratory_rate', 10, 40, $colorRr);

        // Leyenda
        $legendFont = 5;
        $legendY = 18;
        $legendX = $marginLeft;
        $legendItems = [
            ['F.C.', $colorHr],
            ['F.R.', $colorRr],
        ];
        foreach ($legendItems as [$label, $color]) {
            imagefilledrectangle($image, $legendX, $legendY + 4, $legendX + 20, $legendY + 12, $color);
            imagestring($image, $legendFont, $legendX + 26, $legendY, $label, $textDark);
            $legendX += 26 + imagefontwidth($legendFont) * strlen($label) + 26;
        }

        // Título
        imagestring($image, 5, $marginLeft, 0, 'Grafica de signos vitales - Estancia completa', $textDark);

        // Exportar a base64 (en memoria)
        ob_start();
        imagepng($image);
        $pngData = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}
