<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostSurgicalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->isDoctor() || $user->isNurse();
    }

    public function rules(): array
    {
        $rules = [
            'surgery_date'           => ['nullable', 'date'],
            'surgery_time'           => ['nullable', 'string', 'max:10'],
            'surgery_type'           => ['nullable', 'in:urgencia,programada'],
            'preop_diagnosis'        => ['nullable', 'string', 'max:10000'],
            'postop_diagnosis'       => ['nullable', 'string', 'max:10000'],
            'planned_surgery'        => ['nullable', 'string', 'max:5000'],
            'performed_surgery'      => ['nullable', 'string', 'max:5000'],
            'surgical_time'          => ['nullable', 'string', 'max:50'],
            'complications'          => ['nullable', 'string', 'max:5000'],
            'bleeding'               => ['nullable', 'string', 'max:2000'],
            'textile_count'          => ['nullable', 'in:completo,incompleto'],
            'textile_count_detail'   => ['nullable', 'string', 'max:255'],
            'ischemia_time'          => ['nullable', 'string', 'max:50'],
            'patient_status_at_exit' => ['nullable', 'string', 'max:5000'],
            'prognosis'              => ['nullable', 'string', 'max:2000'],
            'surgical_technique'     => ['nullable', 'string', 'max:15000'],
            'surgeon_user_id'        => ['nullable'],
            'surgeon_other_name'     => ['nullable', 'string', 'max:150'],
            'assistant_user_id'      => ['nullable'],
            'assistant_other_name'   => ['nullable', 'string', 'max:150'],
            'anesthesiologist_user_id'    => ['nullable'],
            'anesthesiologist_other_name' => ['nullable', 'string', 'max:150'],
        ];

        if (!auth()->user()->isDoctor()) {
            $rules['attending_doctor_id'] = [
                'required',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'doctor')),
            ];
        }

        return $rules;
    }
}
