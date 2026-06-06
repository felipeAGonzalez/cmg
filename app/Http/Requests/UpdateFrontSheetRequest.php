<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFrontSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización por rol la maneja el middleware de la ruta (admin,nurse).
        return true;
    }

    public function rules(): array
    {
        $services       = array_keys(config('services_catalog'));
        $maritalStatuses = array_keys(config('marital_statuses'));
        $states         = array_keys(config('mexican_states'));

        return [
            'service'         => ['required', Rule::in([...$services, 'other'])],
            'service_other'   => ['nullable', 'required_if:service,other', 'string', 'max:120'],
            'marital_status'  => ['nullable', Rule::in($maritalStatuses)],
            'occupation'      => ['nullable', 'string', 'max:120'],
            'city'            => ['nullable', 'string', 'max:120'],
            'state'           => ['nullable', Rule::in($states)],
            'address'         => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'final_diagnoses' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'service.required'        => 'Debes seleccionar un servicio.',
            'service.in'              => 'El servicio seleccionado no es válido.',
            'service_other.required_if' => 'Especifica el servicio cuando seleccionas "Otro".',
            'marital_status.in'       => 'El estado civil seleccionado no es válido.',
            'state.in'                => 'El estado seleccionado no es válido.',
        ];
    }

    /**
     * Devuelve solo los campos que se persisten en form_data.
     * Limpia service_other cuando el servicio no es "other".
     *
     * @return array<string, mixed>
     */
    public function formData(): array
    {
        $data = $this->only([
            'service',
            'service_other',
            'marital_status',
            'occupation',
            'city',
            'state',
            'address',
            'phone',
            'final_diagnoses',
        ]);

        if (($data['service'] ?? null) !== 'other') {
            $data['service_other'] = null;
        }

        return $data;
    }
}
