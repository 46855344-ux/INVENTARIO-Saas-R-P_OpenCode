<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
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
        $locationId = $this->route('id');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('locations', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($locationId),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'type' => ['sometimes', 'required', 'in:warehouse,zone,shelf,bin,other'],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'not_in:'.$locationId,
                Rule::exists('locations', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'Ya existe una ubicación con ese código en la empresa.',
            'name.required' => 'El nombre es obligatorio.',
            'type.required' => 'El tipo es obligatorio.',
            'type.in' => 'El tipo de ubicación no es válido.',
            'parent_id.not_in' => 'Una ubicación no puede ser su propio padre.',
            'parent_id.exists' => 'La ubicación padre no pertenece a la empresa activa.',
        ];
    }
}
