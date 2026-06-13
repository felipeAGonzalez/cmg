<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreVitalSignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'recorded_at'              => ['required', 'date', 'before_or_equal:now'],
            'heart_rate'               => ['nullable', 'integer', 'min:20', 'max:250'],
            'blood_pressure_systolic'  => ['nullable', 'integer', 'min:40', 'max:300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:20', 'max:200'],
            'respiratory_rate'         => ['nullable', 'integer', 'min:5', 'max:80'],
            'temperature'              => ['nullable', 'numeric', 'min:30', 'max:45'],
            'notes'                    => ['nullable', 'string', 'max:255'],
            'glucose_mg_dl'            => ['nullable', 'integer', 'min:20', 'max:800'],
        ];
    }

    public function messages(): array
    {
        return [
            'recorded_at.required'             => 'La hora de la toma es obligatoria.',
            'recorded_at.date'                 => 'La hora de la toma no es válida.',
            'recorded_at.before_or_equal'      => 'La hora no puede ser futura.',
            'glucose_mg_dl.integer'            => 'La glucemia capilar debe ser un número entero.',
            'glucose_mg_dl.min'                => 'El valor de glucemia es demasiado bajo.',
            'glucose_mg_dl.max'                => 'El valor de glucemia es demasiado alto.',
            'heart_rate.integer'               => 'La frecuencia cardiaca debe ser un número entero.',
            'heart_rate.min'                   => 'La frecuencia cardiaca es demasiado baja.',
            'heart_rate.max'                   => 'La frecuencia cardiaca es demasiado alta.',
            'blood_pressure_systolic.integer'  => 'La tensión sistólica debe ser un número entero.',
            'blood_pressure_diastolic.integer' => 'La tensión diastólica debe ser un número entero.',
            'respiratory_rate.integer'         => 'La frecuencia respiratoria debe ser un número entero.',
            'temperature.numeric'              => 'La temperatura debe ser un número.',
            'temperature.min'                  => 'La temperatura es demasiado baja.',
            'temperature.max'                  => 'La temperatura es demasiado alta.',
            'notes.max'                        => 'Las notas no pueden superar 255 caracteres.',
        ];
    }

    /**
     * Al menos un signo vital numérico debe estar capturado.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $numericFields = [
                'heart_rate',
                'blood_pressure_systolic',
                'blood_pressure_diastolic',
                'respiratory_rate',
                'temperature',
            ];

            $hasAny = collect($numericFields)
                ->contains(fn ($field) => $this->filled($field));

            if (! $hasAny) {
                $validator->errors()->add(
                    'heart_rate',
                    'Captura al menos un signo vital (F.C., T.A., F.R. o temperatura).'
                );
            }
        });
    }
}
