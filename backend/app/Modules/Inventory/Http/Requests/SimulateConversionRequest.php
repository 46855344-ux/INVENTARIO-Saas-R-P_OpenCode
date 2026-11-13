<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimulateConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'from_unit_id' => ['required', 'integer', 'exists:units,id'],
            'to_unit_id' => ['required', 'integer', 'exists:units,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_unit_id.required' => 'La unidad de origen es obligatoria.',
            'from_unit_id.exists' => 'La unidad de origen no existe.',
            'to_unit_id.required' => 'La unidad de destino es obligatoria.',
            'to_unit_id.exists' => 'La unidad de destino no existe.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.numeric' => 'La cantidad debe ser un número.',
            'quantity.min' => 'La cantidad no puede ser negativa.',
        ];
    }
}
