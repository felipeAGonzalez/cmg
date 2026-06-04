<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isNurse() || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'to_room_id' => 'required|exists:rooms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'to_room_id.required' => 'Debe seleccionar el cuarto de destino.',
            'to_room_id.exists'   => 'El cuarto de destino no existe.',
        ];
    }
}
