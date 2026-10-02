<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGlucoseReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'nurse', 'root'], true);
    }

    public function rules(): array
    {
        return [
            'recorded_at' => ['required', 'date', 'before_or_equal:now'],
            'value_mg_dl' => ['required', 'integer', 'min:20', 'max:800'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'recorded_at.required' => 'La hora de la lectura es obligatoria.',
            'recorded_at.date' => 'La hora de la lectura no es válida.',
            'recorded_at.before_or_equal' => 'La hora no puede ser futura.',
            'value_mg_dl.required' => 'El valor de glucemia es obligatorio.',
            'value_mg_dl.integer' => 'La glucemia capilar debe ser un número entero.',
            'value_mg_dl.min' => 'El valor de glucemia es demasiado bajo.',
            'value_mg_dl.max' => 'El valor de glucemia es demasiado alto.',
            'notes.max' => 'Las notas no pueden superar 255 caracteres.',
        ];
    }
}
