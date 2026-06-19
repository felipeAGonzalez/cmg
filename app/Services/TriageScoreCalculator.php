<?php

namespace App\Services;

class TriageScoreCalculator
{
    public static function calculate(array $data): array
    {
        $hasImmediateAlert = ($data['immediate_alert_loss'] ?? false)
            || ($data['immediate_apnea'] ?? false)
            || ($data['immediate_no_pulse'] ?? false)
            || ($data['immediate_intubation'] ?? false)
            || ($data['immediate_angina'] ?? false);

        $aFields = [
            'trauma_score', 'wound_score', 'respiratory_difficulty_score',
            'cyanosis_score', 'paleness_score', 'hemorrhage_score',
            'pain_score', 'intoxication_score', 'seizures_score',
            'glasgow_score', 'dehydration_score', 'psychosis_score',
        ];
        $sumA = 0;
        foreach ($aFields as $field) {
            $sumA += (int) ($data[$field] ?? 0);
        }

        $bFields = ['bp_score', 'hr_score', 'rr_score', 'temp_score', 'glucose_score'];
        $sumB = 0;
        foreach ($bFields as $field) {
            $sumB += (int) ($data[$field] ?? 0);
        }

        $total = $sumA + $sumB;

        if ($hasImmediateAlert) {
            $color = 'red';
        } elseif ($total <= 10) {
            $color = 'blue';
        } elseif ($total <= 20) {
            $color = 'green';
        } elseif ($total <= 30) {
            $color = 'yellow';
        } elseif ($total <= 40) {
            $color = 'orange';
        } else {
            $color = 'red';
        }

        return [
            'sum_a' => $sumA,
            'sum_b' => $sumB,
            'total' => $total,
            'color' => $color,
        ];
    }
}
