<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuspendMedicationOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'doctor', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'suspension_reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'suspension_reason.required' => 'Es obligatorio indicar el motivo de la suspensión.',
            'suspension_reason.min'      => 'El motivo debe tener al menos 5 caracteres.',
            'suspension_reason.max'      => 'El motivo no puede superar 500 caracteres.',
        ];
    }
}
