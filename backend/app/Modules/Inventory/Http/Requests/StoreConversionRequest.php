<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $companyId = TenantContext::fromRequest($this)->company->id;

        return [
            'from_unit_id' => [
                'required',
                'integer',
                'exists:units,id',
                Rule::unique('unit_conversions', 'from_unit_id')
                    ->where('company_id', $companyId)
                    ->where('to_unit_id', $this->input('to_unit_id')),
            ],
            'to_unit_id' => ['required', 'integer', 'different:from_unit_id', 'exists:units,id'],
            'factor' => ['required', 'numeric', 'gt:0'],
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
            'from_unit_id.unique' => 'Ya existe una conversión entre esas unidades en la empresa.',
            'to_unit_id.required' => 'La unidad de destino es obligatoria.',
            'to_unit_id.exists' => 'La unidad de destino no existe.',
            'to_unit_id.different' => 'La unidad de destino debe ser distinta de la de origen.',
            'factor.required' => 'El factor es obligatorio.',
            'factor.numeric' => 'El factor debe ser un número.',
            'factor.gt' => 'El factor debe ser mayor que cero.',
        ];
    }
}
