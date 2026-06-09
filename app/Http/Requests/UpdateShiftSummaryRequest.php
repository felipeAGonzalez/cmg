<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'diet'                        => ['nullable', 'string', 'max:100'],
            'formula'                     => ['nullable', 'string', 'max:100'],
            'oral_liquids_ml'             => ['nullable', 'integer', 'min:0', 'max:10000'],
            'parenteral_liquids_ml'       => ['nullable', 'integer', 'min:0', 'max:10000'],
            'electrolytes_blood_elements' => ['nullable', 'string', 'max:2000'],
            'urine_output_ml'             => ['nullable', 'integer', 'min:0', 'max:10000'],
            'evacuations_count'           => ['nullable', 'integer', 'min:0', 'max:50'],
            'vomit_suction_drainage_ml'   => ['nullable', 'integer', 'min:0', 'max:10000'],
            'lab_biological_products'     => ['nullable', 'string', 'max:2000'],
            'reagents'                    => ['nullable', 'string', 'max:2000'],
            'studies_operations'          => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'diet.max'                  => 'La dieta no puede superar 100 caracteres.',
            'formula.max'               => 'La fórmula no puede superar 100 caracteres.',
            'oral_liquids_ml.integer'   => 'Los líquidos orales deben ser un número entero (ml).',
            'parenteral_liquids_ml.integer' => 'Los líquidos parenterales deben ser un número entero (ml).',
            'urine_output_ml.integer'   => 'La uresis debe ser un número entero (ml).',
            'evacuations_count.integer' => 'Las evacuaciones deben ser un número entero.',
            'vomit_suction_drainage_ml.integer' => 'El drenaje debe ser un número entero (ml).',
            'integer'                   => 'El campo :attribute debe ser un número entero.',
            'min'                       => 'El campo :attribute no puede ser negativo.',
            'max'                       => 'El campo :attribute supera el valor máximo permitido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'oral_liquids_ml'           => 'líquidos orales',
            'parenteral_liquids_ml'     => 'líquidos parenterales',
            'urine_output_ml'           => 'uresis',
            'evacuations_count'         => 'evacuaciones',
            'vomit_suction_drainage_ml' => 'vómito / aspiración / drenaje',
        ];
    }
}
