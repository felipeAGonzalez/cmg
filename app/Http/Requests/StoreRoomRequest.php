<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'number' => 'required|integer|min:1|unique:rooms,number',
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
