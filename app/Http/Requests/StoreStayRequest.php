<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isNurse();
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'last_name_one'  => 'required|string|max:100',
            'last_name_two'  => 'nullable|string|max:100',
            'birth_date'     => 'required|date|before:today',
            'gender'         => 'required|in:M,F',
            'diagnosis'      => 'required|string|max:2000',
            'admission_date' => 'required|date|before_or_equal:now',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'El nombre del paciente es obligatorio.',
            'last_name_one.required'  => 'El primer apellido es obligatorio.',
            'birth_date.required'     => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before'       => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required'         => 'El género es obligatorio.',
            'gender.in'               => 'El género debe ser Masculino o Femenino.',
            'diagnosis.required'      => 'El diagnóstico es obligatorio.',
            'diagnosis.max'           => 'El diagnóstico no puede superar 2000 caracteres.',
            'admission_date.required' => 'La fecha de ingreso es obligatoria.',
            'admission_date.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
        ];
    }
}
