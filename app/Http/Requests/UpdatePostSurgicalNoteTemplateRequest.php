<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostSurgicalNoteTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->isDoctor();
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:100'],
            'description'            => ['nullable', 'string', 'max:500'],
            'preop_diagnosis'        => ['nullable', 'string', 'max:10000'],
            'postop_diagnosis'       => ['nullable', 'string', 'max:10000'],
            'planned_surgery'        => ['nullable', 'string', 'max:5000'],
            'performed_surgery'      => ['nullable', 'string', 'max:5000'],
            'complications'          => ['nullable', 'string', 'max:5000'],
            'bleeding'               => ['nullable', 'string', 'max:2000'],
            'patient_status_at_exit' => ['nullable', 'string', 'max:5000'],
            'prognosis'              => ['nullable', 'string', 'max:2000'],
            'surgical_technique'     => ['nullable', 'string', 'max:15000'],
        ];
    }
}
