<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($this->route('id')),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'dimension' => ['sometimes', 'required', 'in:mass,volume,count,length,time,other'],
            'precision' => ['sometimes', 'required', 'integer', 'min:0', 'max:4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'Ya existe una unidad con ese código en la empresa.',
            'name.required' => 'El nombre es obligatorio.',
            'dimension.required' => 'La dimensión es obligatoria.',
            'dimension.in' => 'La dimensión no es válida.',
            'precision.min' => 'La precisión debe estar entre 0 y 4.',
            'precision.max' => 'La precisión debe estar entre 0 y 4.',
        ];
    }
}
