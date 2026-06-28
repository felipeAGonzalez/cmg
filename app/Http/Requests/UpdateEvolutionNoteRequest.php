<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvolutionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    public function rules(): array
    {
        $rules = [
            'note_datetime'    => ['required', 'date'],
            'antecedents'      => ['nullable', 'string', 'max:10000'],
            'subjective'       => ['nullable', 'string', 'max:10000'],
            'objective'        => ['nullable', 'string', 'max:10000'],
            'analysis'         => ['nullable', 'string', 'max:10000'],
            'diagnosis'        => ['nullable', 'string', 'max:5000'],
            'prognosis'        => ['nullable', 'string', 'max:5000'],
            'plan'             => ['nullable', 'string', 'max:10000'],
            'medications_from' => ['nullable', 'date'],
            'medications_to'   => ['nullable', 'date', 'after_or_equal:medications_from'],
        ];

        $user = auth()->user();
        if ($user && !$user->isDoctor()) {
            $rules['attending_doctor_id'] = [
                'required',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'doctor')),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'note_datetime.required'        => 'La fecha y hora de la nota son obligatorias.',
            'attending_doctor_id.required'  => 'Debes seleccionar un médico tratante.',
            'attending_doctor_id.exists'    => 'El médico seleccionado no es válido.',
            'medications_to.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha inicio.',
        ];
    }
}
