<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $rules = [
            'name'            => 'required|string|max:100',
            'last_name_one'   => 'required|string|max:100',
            'last_name_two'   => 'nullable|string|max:100',
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password'        => 'nullable|string|min:8|confirmed',
            'role'            => ['required', Rule::in(['admin', 'doctor', 'nurse'])],
            'specialty_ids'   => ['nullable', 'array'],
            'specialty_ids.*' => ['integer', Rule::exists('specialties', 'id')],
        ];

        if (in_array($this->input('role'), ['doctor', 'nurse'])) {
            $rules['professional_license'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s\-]+$/'];
        } else {
            $rules['professional_license'] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'El nombre es obligatorio.',
            'name.max'               => 'El nombre no puede superar 100 caracteres.',
            'last_name_one.required' => 'El primer apellido es obligatorio.',
            'email.required'         => 'El correo electrónico es obligatorio.',
            'email.email'            => 'El correo electrónico no tiene un formato válido.',
            'email.unique'           => 'Este correo electrónico ya está registrado.',
            'password.min'           => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'     => 'Las contraseñas no coinciden.',
            'role.required'          => 'El rol es obligatorio.',
            'role.in'                => 'El rol seleccionado no es válido.',
            'specialty_ids.*.exists' => 'Una de las especialidades seleccionadas no es válida.',
            'professional_license.required' => 'La cédula profesional es obligatoria para médicos y enfermería.',
            'professional_license.max'      => 'La cédula no puede exceder 20 caracteres.',
            'professional_license.regex'    => 'La cédula solo puede contener letras, números, espacios y guiones.',
        ];
    }
}
