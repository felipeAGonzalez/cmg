<?php

namespace App\Http\Requests;

use App\Enums\DoctorSpecialty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $specialtyValues = implode(',', array_column(DoctorSpecialty::cases(), 'value'));

        return [
            'name'          => 'required|string|max:100',
            'last_name_one' => 'required|string|max:100',
            'last_name_two' => 'nullable|string|max:100',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'role'          => ['required', Rule::in(['admin', 'doctor', 'nurse'])],
            'specialty'     => ["required_if:role,doctor", 'nullable', "in:{$specialtyValues}"],
        ];
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
            'password.required'      => 'La contraseña es obligatoria.',
            'password.min'           => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'     => 'Las contraseñas no coinciden.',
            'role.required'          => 'El rol es obligatorio.',
            'role.in'                => 'El rol seleccionado no es válido.',
            'specialty.required_if'  => 'La especialidad es obligatoria para médicos.',
            'specialty.in'           => 'La especialidad seleccionada no es válida.',
        ];
    }
}
