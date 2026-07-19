<?php

namespace App\Services;

use Illuminate\Support\Collection;

class AnesthesiaVitalsChartGenerator
{
    protected int $width               = 700;
    protected int $chartHeight         = 300;
    protected int $legendHeight        = 28;
    protected int $paddingLeft         = 40;
    protected int $paddingRight        = 45;
    protected int $paddingTop          = 10;
    protected int $paddingBottomLabels = 20;
    protected int $maxValue            = 220;

    public function generate(Collection $readings): string
    {
        $totalHeight = $this->chartHeight + $this->legendHeight;
        $im = imagecreatetruecolor($this->width, $totalHeight);

        $white           = imagecolorallocate($im, 255, 255, 255);
        $gridGray        = imagecolorallocate($im, 221, 221, 221);
        $textGray        = imagecolorallocate($im, 90,  90,  90);
        $blue            = imagecolorallocate($im, 33,  150, 243);
        $pink            = imagecolorallocate($im, 233, 30,  99);
        $green           = imagecolorallocate($im, 76,  175, 80);
        $orange          = imagecolorallocate($im, 255, 152, 0);
        $minuteGridColor = imagecolorallocate($im, 235, 235, 235);
        $hourGridColor   = imagecolorallocate($im, 160, 160, 160);

        imagefilledrectangle($im, 0, 0, $this->width, $totalHeight, $white);

        $innerW = $this->width - $this->paddingLeft - $this->paddingRight;
        $innerH = $this->chartHeight - $this->paddingTop - $this->paddingBottomLabels;
        $count  = $readings->count();

        // Ordenar cronológicamente por tiempo real
        $arr = $readings->values()->sortBy(fn($r) => $this->toMinutes($r->reading_time))->values();

        // Rango de tiempo real (con soporte para cruce de medianoche)
        $firstMinutes  = $count > 0 ? $this->toMinutes($arr->first()->reading_time) : 0;
        $lastMinutesRaw = $count > 0 ? $this->toMinutes($arr->last()->reading_time) : 0;
        $lastMinutes   = $lastMinutesRaw < $firstMinutes ? $lastMinutesRaw + 1440 : $lastMinutesRaw;
        $totalMinutes  = max(1, $lastMinutes - $firstMinutes);

        // Posicionamiento X basado en tiempo real
        $elapsedFor = function ($r) use ($firstMinutes): int {
            $m = $this->toMinutes($r->reading_time);
            if ($m < $firstMinutes) $m += 1440;
            return $m - $firstMinutes;
        };

        $xForElapsed = function (int $elapsed) use ($innerW, $totalMinutes): int {
            return (int) round($this->paddingLeft + ($elapsed / $totalMinutes) * $innerW);
        };

        $xForReading = fn($r) => $xForElapsed($elapsedFor($r));

        // Eje Y (signos vitales)
        $yFor = function (?float $v) use ($innerH): ?int {
            if ($v === null) return null;
            return (int) round($this->paddingTop + $innerH - ($v / $this->maxValue) * $innerH);
        };

        // Acumulado de suero (sobre el arreglo ya ordenado)
        $cumulativeSerum = [];
        $running = 0;
        foreach ($arr as $r) {
            $running += ($r->hartmann_ml ?? 0) + ($r->glucose_ml ?? 0) + ($r->nacl_ml ?? 0);
            $cumulativeSerum[] = $running;
        }
        $maxSerum      = max($cumulativeSerum ?: [0]);
        $hasSerumData  = $maxSerum > 0;
        $serumScaleMax = $this->niceRoundUp($maxSerum);

        $yForSerum = function (int $val) use ($innerH, $serumScaleMax): int {
            if ($serumScaleMax <= 0) return $this->paddingTop + $innerH;
            return (int) round($this->paddingTop + $innerH - ($val / $serumScaleMax) * $innerH);
        };

        // ===== Cuadrícula horizontal + etiquetas eje izquierdo (0–220) =====
        for ($v = 0; $v <= $this->maxValue; $v += 20) {
            $y = $yFor((float) $v);
            imageline($im, $this->paddingLeft, $y, $this->width - $this->paddingRight, $y, $gridGray);
            imagestring($im, 1, 2, $y - 6, (string) $v, $textGray);
        }

        // ===== Cuadrícula vertical basada en tiempo real (cada 5 min) =====
        for ($elapsed = 0; $elapsed <= $totalMinutes; $elapsed += 5) {
            $x              = $xForElapsed($elapsed);
            $minuteOfDay    = ($firstMinutes + $elapsed) % 1440;
            $hour           = intdiv($minuteOfDay, 60);
            $minuteOfHour   = $minuteOfDay % 60;

            if ($minuteOfHour === 0) {
                imageline($im, $x, $this->paddingTop, $x, $this->chartHeight - $this->paddingBottomLabels, $hourGridColor);
                imagestring($im, 3, $x - 6, $this->chartHeight - $this->paddingBottomLabels + 4,
                    str_pad((string) $hour, 2, '0', STR_PAD_LEFT), $textGray);
            } else {
                imageline($im, $x, $this->paddingTop, $x, $this->chartHeight - $this->paddingBottomLabels, $minuteGridColor);
            }
        }

        // Línea horizontal separando área de gráfica y etiquetas de hora
        imageline(
            $im,
            $this->paddingLeft, $this->chartHeight - $this->paddingBottomLabels,
            $this->width - $this->paddingRight, $this->chartHeight - $this->paddingBottomLabels,
            $hourGridColor
        );

        // ===== Etiquetas eje derecho (suero acumulado, ml) =====
        if ($hasSerumData) {
            $serumStep = $serumScaleMax / 5;
            for ($s = 0; $s <= 5; $s++) {
                $val = (int) round($serumStep * $s);
                $y   = $yForSerum($val);
                imagestring($im, 1, $this->width - $this->paddingRight + 4, $y - 5, (string) $val, $orange);
            }
            imagestring($im, 1, $this->width - $this->paddingRight + 4, $this->paddingTop - 8, 'ml', $orange);
        }

        // ===== TA: triángulo ▼ sistólica + triángulo ▲ diastólica + línea de rango =====
        foreach ($arr as $r) {
            if ($r->ta_sys === null || $r->ta_dia === null) continue;
            $x    = $xForReading($r);
            $ySys = $yFor((float) $r->ta_sys);
            $yDia = $yFor((float) $r->ta_dia);
            imageline($im, $x, $ySys, $x, $yDia, $blue);
            imagefilledpolygon($im, [$x - 4, $ySys - 7, $x + 4, $ySys - 7, $x, $ySys], 3, $blue);
            imagefilledpolygon($im, [$x - 4, $yDia + 7, $x + 4, $yDia + 7, $x, $yDia], 3, $blue);
        }

        // ===== FC: línea de tendencia + puntos rellenos =====
        $fcPoints = [];
        foreach ($arr as $r) {
            if ($r->fc !== null) $fcPoints[] = [$xForReading($r), $yFor((float) $r->fc)];
        }
        for ($i = 0; $i < count($fcPoints) - 1; $i++) {
            imageline($im, $fcPoints[$i][0], $fcPoints[$i][1], $fcPoints[$i + 1][0], $fcPoints[$i + 1][1], $pink);
        }
        foreach ($fcPoints as $p) {
            imagefilledellipse($im, $p[0], $p[1], 7, 7, $pink);
        }

        // ===== SpO2: línea de tendencia + círculos sin relleno =====
        $spo2Points = [];
        foreach ($arr as $r) {
            if ($r->spo2 !== null) $spo2Points[] = [$xForReading($r), $yFor((float) $r->spo2)];
        }
        for ($i = 0; $i < count($spo2Points) - 1; $i++) {
            imageline($im, $spo2Points[$i][0], $spo2Points[$i][1], $spo2Points[$i + 1][0], $spo2Points[$i + 1][1], $green);
        }
        foreach ($spo2Points as $p) {
            imageellipse($im, $p[0], $p[1], 9, 9, $green);
        }

        // ===== Suero acumulado: línea punteada naranja + diamantes =====
        if ($hasSerumData) {
            $serumPoints = [];
            foreach ($arr as $i => $r) {
                $serumPoints[] = [$xForReading($r), $yForSerum($cumulativeSerum[$i])];
            }
            for ($i = 0; $i < count($serumPoints) - 1; $i++) {
                $this->drawDashedLine(
                    $im,
                    $serumPoints[$i][0], $serumPoints[$i][1],
                    $serumPoints[$i + 1][0], $serumPoints[$i + 1][1],
                    $orange
                );
            }
            foreach ($serumPoints as $p) {
                $this->drawDiamond($im, $p[0], $p[1], 5, $orange);
            }
        }

        // ===== Leyenda =====
        $ly = $this->chartHeight + 10;

        imagefilledpolygon($im, [28, $ly - 4, 36, $ly - 4, 32, $ly + 2], 3, $blue);
        imagefilledpolygon($im, [28, $ly + 8,  36, $ly + 8,  32, $ly + 2], 3, $blue);
        imagestring($im, 1, 42, $ly - 2, 'TA (sistolica/diastolica)', $textGray);

        imagefilledellipse($im, 220, $ly + 2, 8, 8, $pink);
        imagestring($im, 1, 230, $ly - 2, 'FC', $textGray);

        imageellipse($im, 270, $ly + 2, 10, 10, $green);
        imagestring($im, 1, 282, $ly - 2, 'SpO2', $textGray);

        if ($hasSerumData) {
            $this->drawDiamond($im, 330, $ly + 2, 5, $orange);
            imagestring($im, 1, 342, $ly - 2, 'Suero acumulado (ml)', $orange);
        }

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    protected function toMinutes($time): int
    {
        $s = is_string($time) ? $time : $time->format('H:i');
        [$h, $m] = explode(':', $s);
        return ((int) $h) * 60 + (int) $m;
    }

    protected function niceRoundUp(int $value): int
    {
        if ($value <= 0)    return 0;
        if ($value <= 100)  return 100;
        if ($value <= 250)  return 250;
        if ($value <= 500)  return 500;
        if ($value <= 1000) return 1000;
        if ($value <= 2000) return 2000;
        return (int) (ceil($value / 1000) * 1000);
    }

    protected function drawDashedLine($im, int $x1, int $y1, int $x2, int $y2, $color): void
    {
        $dashLen  = 4;
        $gapLen   = 3;
        $distance = sqrt(($x2 - $x1) ** 2 + ($y2 - $y1) ** 2);
        if ($distance == 0) return;

        $dx      = ($x2 - $x1) / $distance;
        $dy      = ($y2 - $y1) / $distance;
        $pos     = 0;
        $drawing = true;

        while ($pos < $distance) {
            $segLen = $drawing ? $dashLen : $gapLen;
            $endPos = min($pos + $segLen, $distance);
            if ($drawing) {
                imageline(
                    $im,
                    (int) round($x1 + $dx * $pos),   (int) round($y1 + $dy * $pos),
                    (int) round($x1 + $dx * $endPos), (int) round($y1 + $dy * $endPos),
                    $color
                );
            }
            $pos     = $endPos;
            $drawing = !$drawing;
        }
    }

    protected function drawDiamond($im, int $cx, int $cy, int $size, $color): void
    {
        imagefilledpolygon($im, [
            $cx,         $cy - $size,
            $cx + $size, $cy,
            $cx,         $cy + $size,
            $cx - $size, $cy,
        ], 4, $color);
    }
}
