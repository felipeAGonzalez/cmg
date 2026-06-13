<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnesthesiaConsentRequest extends FormRequest
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
            'patient_phone'                    => ['nullable', 'string', 'max:30'],

            'responsible_name'                 => ['nullable', 'string', 'max:200'],
            'responsible_relationship'         => ['nullable', 'string', 'max:100'],
            'responsible_phone'                => ['nullable', 'string', 'max:30'],
            'responsible_address'              => ['nullable', 'string', 'max:500'],

            'anesthesiologist_name'            => ['required', 'string', 'max:200'],
            'anesthesiologist_state'           => ['required', 'string', 'max:100'],

            'procedure_name'                   => ['required', 'string', 'max:500'],

            'anesthesia_type'                  => ['required', 'string', 'max:200'],
            'anesthesia_character'             => ['required', Rule::in(['elective', 'urgent'])],

            'witness_1_name'                   => ['required', 'string', 'max:200'],
            'witness_2_name'                   => ['nullable', 'string', 'max:200'],

            'negation'                         => ['nullable', 'array'],
            'negation.applies'                 => ['nullable', 'boolean'],

            'revocation'                       => ['nullable', 'array'],
            'revocation.applies'               => ['nullable', 'boolean'],
            'revocation.original_consent_date' => ['nullable', 'date', 'required_if:revocation.applies,1'],
            'revocation.revocation_date'       => ['nullable', 'date', 'required_if:revocation.applies,1'],
        ];

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
            'anesthesiologist_name.required'            => 'El nombre del anestesiólogo es obligatorio.',
            'anesthesiologist_state.required'           => 'El estado del anestesiólogo es obligatorio.',
            'procedure_name.required'                   => 'El nombre del procedimiento es obligatorio.',
            'anesthesia_type.required'                  => 'El tipo de anestesia es obligatorio.',
            'anesthesia_character.required'             => 'Indica si la anestesia es electiva o urgente.',
            'anesthesia_character.in'                   => 'El carácter de la anestesia no es válido.',
            'witness_1_name.required'                   => 'El nombre del primer testigo es obligatorio.',
            'revocation.original_consent_date.required_if' => 'Indica la fecha del consentimiento original que se revoca.',
            'revocation.revocation_date.required_if'    => 'Indica la fecha de la revocación.',
            'prescribed_by_id.required'                 => 'Selecciona el médico responsable.',
        ];
    }
}
