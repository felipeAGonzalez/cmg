<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransfusionChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'doctor', 'nurse']);
    }

    public function rules(): array
    {
        return [
            'folio' => ['nullable', 'string', 'max:50'],
        ];
    }
}
