<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DischargeStayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'discharge_reason' => [
                'required',
                Rule::in(array_keys(config('discharge_reasons'))),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'discharge_reason.required' => 'Debes seleccionar el motivo del alta.',
            'discharge_reason.in'       => 'El motivo de alta seleccionado no es válido.',
        ];
    }
}
