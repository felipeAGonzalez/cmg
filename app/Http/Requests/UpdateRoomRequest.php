<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'min:1', Rule::unique('rooms', 'number')->ignore($this->route('room'))],
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'El número de cuarto es obligatorio.',
            'number.integer'  => 'El número de cuarto debe ser un entero.',
            'number.min'      => 'El número de cuarto debe ser mayor a 0.',
            'number.unique'   => 'Ya existe un cuarto con ese número.',
        ];
    }
}
