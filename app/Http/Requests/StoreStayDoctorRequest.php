<?php

namespace App\Http\Requests;

use App\Enums\DoctorSpecialty;
use Illuminate\Foundation\Http\FormRequest;

class StoreStayDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isNurse();
    }

    public function rules(): array
    {
        $specialtyValues = implode(',', array_column(DoctorSpecialty::cases(), 'value'));

        return [
            'doctor_id' => 'required|exists:users,id',
            'specialty' => "required|in:{$specialtyValues}",
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Debe seleccionar un médico.',
            'doctor_id.exists'   => 'El médico seleccionado no existe.',
            'specialty.required' => 'La especialidad es obligatoria.',
            'specialty.in'       => 'La especialidad seleccionada no es válida.',
        ];
    }
}
