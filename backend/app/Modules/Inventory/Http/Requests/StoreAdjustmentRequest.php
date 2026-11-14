<?php

namespace App\Modules\Inventory\Http\Requests;

class StoreAdjustmentRequest extends StoreMovementRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'direction' => ['required', 'in:in,out'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'direction.required' => 'El sentido del ajuste es obligatorio.',
            'direction.in' => 'El sentido debe ser «in» o «out».',
        ]);
    }
}
