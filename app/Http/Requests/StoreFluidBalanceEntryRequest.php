<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreFluidBalanceEntryRequest extends FormRequest
{
    /** Campos numéricos de ingresos y egresos medibles. */
    public const NUMERIC_FIELDS = [
        'oral_ml', 'iv_solution_ml', 'blood_ml', 'plasma_ml', 'sonda_ml', 'other_inputs_ml',
        'urine_ml', 'evacuation_ml', 'vomit_ml', 'hemorrhage_ml', 'suction_ml', 'canalization_ml',
    ];

    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        $intCol = ['nullable', 'integer', 'min:0', 'max:10000'];

        $rules = ['recorded_at' => ['required', 'date', 'before_or_equal:now']];

        foreach (self::NUMERIC_FIELDS as $field) {
            $rules[$field] = $intCol;
        }

        $rules['observation'] = ['nullable', 'string', 'max:500'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'recorded_at.required'        => 'La hora de la toma es obligatoria.',
            'recorded_at.before_or_equal' => 'La hora de la toma no puede ser futura.',
            '*.integer'                   => 'Los valores deben ser números enteros.',
            '*.min'                       => 'Los valores no pueden ser negativos.',
            '*.max'                       => 'Los valores son demasiado altos. Verifica la unidad (ml).',
            'observation.max'             => 'La observación no puede exceder 500 caracteres.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Al menos un campo numérico debe tener valor > 0.
            foreach (self::NUMERIC_FIELDS as $field) {
                if ((int) $this->input($field, 0) > 0) {
                    return;
                }
            }

            $validator->errors()->add(
                'oral_ml',
                'Debe capturar al menos un valor numérico de ingreso o egreso.'
            );
        });
    }
}
