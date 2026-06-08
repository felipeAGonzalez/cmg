<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicationAdministrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        // No se permite cambiar medication_order_id ni administered_at al editar.
        return [
            'actual_dose'  => ['required', 'string', 'max:80'],
            'status'       => ['required', Rule::in(array_keys(config('administration_statuses')))],
            'reason'       => ['nullable', 'required_unless:status,administered', 'string', 'min:3', 'max:500'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_dose.required'   => 'La dosis administrada es obligatoria.',
            'status.required'        => 'Selecciona el estado de la administración.',
            'status.in'              => 'El estado seleccionado no es válido.',
            'reason.required_unless' => 'El motivo es obligatorio cuando la dosis no fue administrada.',
            'reason.min'             => 'El motivo debe tener al menos 3 caracteres.',
        ];
    }
}
