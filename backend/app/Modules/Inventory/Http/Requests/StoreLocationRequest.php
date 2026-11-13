<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
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
                'max:30',
                Rule::unique('locations', 'code')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:warehouse,zone,shelf,bin,other'],
            'parent_id' => [
                'nullable',
                'integer',
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
            'code.max' => 'El código no puede superar los 30 caracteres.',
            'code.unique' => 'Ya existe una ubicación con ese código en la empresa.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 150 caracteres.',
            'type.required' => 'El tipo es obligatorio.',
            'type.in' => 'El tipo de ubicación no es válido.',
            'parent_id.exists' => 'La ubicación padre no pertenece a la empresa activa.',
        ];
    }
}
