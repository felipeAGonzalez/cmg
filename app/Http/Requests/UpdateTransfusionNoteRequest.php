<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransfusionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    public function rules(): array
    {
        $rules = [
            'transfusion_checklist_id'   => ['nullable', 'exists:transfusion_checklists,id'],
            'start_datetime'             => ['nullable', 'date'],
            'end_datetime'               => ['nullable', 'date', 'after_or_equal:start_datetime'],
            'diagnoses_and_indication'   => ['nullable', 'string', 'max:10000'],
            'compatibility_verification' => ['nullable', 'string', 'max:5000'],
            'evolution_narrative'        => ['nullable', 'string', 'max:10000'],
            'conclusion'                 => ['nullable', 'string', 'max:5000'],
            'pre_ta'                     => ['nullable', 'string', 'max:20'],
            'pre_fc'                     => ['nullable', 'string', 'max:10'],
            'pre_fr'                     => ['nullable', 'string', 'max:10'],
            'pre_temp'                   => ['nullable', 'string', 'max:10'],
            'pre_spo2'                   => ['nullable', 'string', 'max:10'],
            'post_ta'                    => ['nullable', 'string', 'max:20'],
            'post_fc'                    => ['nullable', 'string', 'max:10'],
            'post_fr'                    => ['nullable', 'string', 'max:10'],
            'post_temp'                  => ['nullable', 'string', 'max:10'],
            'post_spo2'                  => ['nullable', 'string', 'max:10'],
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
            'attending_doctor_id.required' => 'Debes seleccionar un médico tratante.',
            'attending_doctor_id.exists'   => 'El médico seleccionado no es válido.',
            'end_datetime.after_or_equal'  => 'La hora de término debe ser posterior o igual a la de inicio.',
        ];
    }
}
