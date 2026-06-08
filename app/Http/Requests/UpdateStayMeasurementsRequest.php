<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStayMeasurementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'height_cm' => ['nullable', 'numeric', 'min:20', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'height_cm.numeric' => 'La talla debe ser un número.',
            'height_cm.min'     => 'La talla debe ser de al menos 20 cm.',
            'height_cm.max'     => 'La talla no puede superar los 250 cm.',
            'weight_kg.numeric' => 'El peso debe ser un número.',
            'weight_kg.min'     => 'El peso debe ser de al menos 0.5 kg.',
            'weight_kg.max'     => 'El peso no puede superar los 500 kg.',
        ];
    }
}
