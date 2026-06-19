<?php

namespace App\Http\Requests;

use App\Models\Stay;
use Illuminate\Foundation\Http\FormRequest;

class HospitalizeFromTriageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    public function rules(): array
    {
        return [
            'room_id' => [
                'required',
                'exists:rooms,id',
                function ($attribute, $value, $fail) {
                    if (Stay::where('room_id', $value)->whereNull('discharge_date')->exists()) {
                        $fail('El cuarto seleccionado ya está ocupado.');
                    }
                },
            ],
            'diagnosis' => ['required', 'string', 'max:500'],
            'height_cm' => ['nullable', 'integer', 'min:30', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Debes seleccionar un cuarto.',
            'room_id.exists' => 'El cuarto seleccionado no existe.',
            'diagnosis.required' => 'El diagnóstico inicial es obligatorio.',
        ];
    }
}
