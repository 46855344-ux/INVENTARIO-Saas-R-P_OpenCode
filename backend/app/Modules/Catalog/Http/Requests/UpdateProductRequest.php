<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('products', 'name')
                    ->where('company_id', $companyId)
                    ->ignore($this->route('id')),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'brand_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('brands', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre no puede superar los 150 caracteres.',
            'name.unique' => 'Ya existe un producto con ese nombre en la empresa.',
            'description.max' => 'La descripción no puede superar los 2000 caracteres.',
            'category_id.exists' => 'La categoría no pertenece a la empresa activa.',
            'brand_id.exists' => 'La marca no pertenece a la empresa activa.',
        ];
    }
}
