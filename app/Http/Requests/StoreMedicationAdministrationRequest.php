<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationAdministrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        $stay = $this->route('stay');

        return [
            'medication_order_id' => [
                'required', 'integer',
                Rule::exists('medication_orders', 'id')->where('stay_id', $stay->id),
            ],
            'administered_at' => ['required', 'date', 'before_or_equal:now'],
            'actual_dose'     => ['required', 'string', 'max:80'],
            'status'          => ['required', Rule::in(array_keys(config('administration_statuses')))],
            'reason'          => ['nullable', 'required_unless:status,administered', 'string', 'min:3', 'max:500'],
            'observations'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'medication_order_id.required' => 'Selecciona la prescripción a administrar.',
            'medication_order_id.exists'   => 'La prescripción seleccionada no pertenece a este paciente.',
            'administered_at.required'     => 'La fecha y hora de administración es obligatoria.',
            'administered_at.before_or_equal' => 'La fecha y hora no puede ser futura.',
            'actual_dose.required'         => 'La dosis administrada es obligatoria.',
            'status.required'              => 'Selecciona el estado de la administración.',
            'status.in'                    => 'El estado seleccionado no es válido.',
            'reason.required_unless'       => 'El motivo es obligatorio cuando la dosis no fue administrada.',
            'reason.min'                   => 'El motivo debe tener al menos 3 caracteres.',
        ];
    }
}
