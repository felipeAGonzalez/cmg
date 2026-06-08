<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'doctor', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'medication_name' => ['required', 'string', 'max:150'],
            'dose'            => ['required', 'string', 'max:80'],
            'route'           => ['required', Rule::in(array_keys(config('medication_routes')))],
            'route_other'     => ['required_if:route,other', 'nullable', 'string', 'max:100'],
            'frequency'       => ['required', Rule::in(array_keys(config('medication_frequencies')))],
            'frequency_other' => ['required_if:frequency,other', 'nullable', 'string', 'max:100'],
            'start_date'      => ['required', 'date'],
            'duration_days'   => ['nullable', 'integer', 'min:1', 'max:365'],
            'indications'     => ['nullable', 'string', 'max:1000'],
        ];

        // El doctor prescribe siempre a su propio nombre (asignado en el
        // controlador). Solo nurse/admin/root eligen el médico prescriptor.
        if ($user && ! $user->isDoctor()) {
            $rules['prescribed_by_id'] = [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'doctor')),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'medication_name.required'    => 'El nombre del medicamento es obligatorio.',
            'dose.required'               => 'La dosis es obligatoria.',
            'route.required'              => 'Selecciona la vía de administración.',
            'route.in'                    => 'La vía seleccionada no es válida.',
            'route_other.required_if'     => 'Especifica la vía cuando seleccionas "Otra".',
            'frequency.required'          => 'Selecciona la frecuencia.',
            'frequency.in'                => 'La frecuencia seleccionada no es válida.',
            'frequency_other.required_if' => 'Especifica la frecuencia cuando seleccionas "Otra".',
            'start_date.required'         => 'La fecha de inicio es obligatoria.',
            'duration_days.integer'       => 'La duración debe ser un número entero de días.',
            'duration_days.min'           => 'La duración debe ser de al menos 1 día.',
            'duration_days.max'           => 'La duración no puede superar 365 días.',
            'prescribed_by_id.required'   => 'Selecciona el médico que prescribe.',
            'prescribed_by_id.exists'     => 'El médico seleccionado no es válido.',
        ];
    }
}
