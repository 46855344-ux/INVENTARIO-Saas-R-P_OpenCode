<?php

namespace App\Modules\Inventory\Http\Requests;

class StoreReturnRequest extends StoreMovementRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'original_movement_id' => ['nullable', 'integer', 'exists:stock_movements,id'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'original_movement_id.exists' => 'El movimiento de origen no existe.',
        ]);
    }
}
