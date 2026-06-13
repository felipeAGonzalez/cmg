<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGlucoseMonitoringOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'doctor', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'start_date'           => ['required', 'date', 'before_or_equal:today'],
            'schedule_description' => ['nullable', 'string', 'max:200'],
            'clinical_reason'      => ['nullable', 'string', 'max:500'],
        ];

        // Solo el no-doctor (nurse/admin) elige el médico prescriptor; el
        // doctor siempre prescribe a su propio nombre (asignado en el controlador).
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
            'start_date.required'         => 'La fecha de inicio es obligatoria.',
            'start_date.before_or_equal'  => 'La fecha de inicio no puede ser futura.',
            'schedule_description.max'    => 'El esquema no puede exceder 200 caracteres.',
            'clinical_reason.max'         => 'El motivo clínico no puede exceder 500 caracteres.',
            'prescribed_by_id.required'   => 'Selecciona el médico que prescribe.',
            'prescribed_by_id.exists'     => 'El médico seleccionado no es válido.',
        ];
    }
}
