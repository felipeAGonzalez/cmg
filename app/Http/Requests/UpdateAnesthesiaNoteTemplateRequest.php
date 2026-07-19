<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnesthesiaNoteTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->isDoctor();
    }

    public function rules(): array
    {
        return [
            'name'                           => ['required', 'string', 'max:100'],
            'description'                    => ['nullable', 'string', 'max:500'],
            'current_illness'                => ['nullable', 'string', 'max:15000'],
            'anesthetic_plan'                => ['nullable', 'string', 'max:10000'],
            'anesthetic_technique_and_drugs' => ['nullable', 'string', 'max:15000'],
            'evolution_and_ucpa_discharge'   => ['nullable', 'string', 'max:10000'],
            'postop_pain_control'            => ['nullable', 'string', 'max:5000'],
        ];
    }
}
