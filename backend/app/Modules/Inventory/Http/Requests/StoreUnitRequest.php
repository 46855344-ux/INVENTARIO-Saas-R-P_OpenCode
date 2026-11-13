<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'dimension' => ['required', 'in:mass,volume,count,length,time,other'],
            'precision' => ['required', 'integer', 'min:0', 'max:4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.max' => 'El código no puede superar los 20 caracteres.',
            'code.unique' => 'Ya existe una unidad con ese código en la empresa.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'dimension.required' => 'La dimensión es obligatoria.',
            'dimension.in' => 'La dimensión no es válida.',
            'precision.required' => 'La precisión es obligatoria.',
            'precision.min' => 'La precisión debe estar entre 0 y 4.',
            'precision.max' => 'La precisión debe estar entre 0 y 4.',
        ];
    }
}
