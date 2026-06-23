<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuthorizedConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && in_array($user->role, ['admin', 'doctor', 'nurse'], true);
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'folio'                    => ['nullable', 'string', 'max:50'],

            'patient_phone'            => ['nullable', 'string', 'max:30'],
            'patient_parentesco'       => ['nullable', 'string', 'max:100'],

            'responsible_name'         => ['required', 'string', 'max:200'],
            'responsible_relationship' => ['required', 'string', 'max:100'],
            'responsible_phone'        => ['nullable', 'string', 'max:30'],
            'responsible_address'      => ['nullable', 'string', 'max:500'],

            'doctor_name'              => ['required', 'string', 'max:200'],

            'diagnoses'                => ['nullable', 'array', 'max:2'],
            'diagnoses.*'              => ['nullable', 'string', 'max:500'],

            'surgical_procedure'       => ['nullable', 'string', 'max:500'],
            'invasive_procedure'       => ['nullable', 'string', 'max:500'],

            'benefits'                 => ['nullable', 'array', 'max:3'],
            'benefits.*'               => ['nullable', 'string', 'max:500'],

            'risks'                    => ['nullable', 'array', 'max:3'],
            'risks.*'                  => ['nullable', 'string', 'max:500'],

            'alternatives'             => ['nullable', 'string', 'max:1000'],

            'designated_person'        => ['nullable', 'string', 'max:200'],

            'city'                     => ['required', 'string', 'max:100'],
            'signed_day'               => ['required', 'integer', 'min:1', 'max:31'],
            'signed_month'             => ['required', 'string', 'max:30'],
            'signed_year'              => ['required', 'integer', 'min:2020', 'max:2100'],
            'signed_time'              => ['required', 'string', 'max:5'],

            'witness_1_name'           => ['required', 'string', 'max:200'],
            'witness_2_name'           => ['required', 'string', 'max:200'],
        ];

        // El doctor firma a su nombre; nurse/admin deben elegir el médico responsable.
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
            'responsible_name.required'         => 'El nombre del responsable es obligatorio.',
            'responsible_relationship.required' => 'La relación con el paciente es obligatoria.',
            'doctor_name.required'              => 'El nombre del médico es obligatorio.',
            'witness_1_name.required'           => 'El nombre del primer testigo es obligatorio.',
            'witness_2_name.required'           => 'El nombre del segundo testigo es obligatorio.',
            'city.required'                     => 'La ciudad es obligatoria.',
            'signed_day.required'               => 'El día de firma es obligatorio.',
            'signed_month.required'             => 'El mes de firma es obligatorio.',
            'signed_year.required'              => 'El año de firma es obligatorio.',
            'signed_time.required'              => 'La hora de firma es obligatoria.',
            'prescribed_by_id.required'         => 'Selecciona el médico responsable.',
        ];
    }
}
