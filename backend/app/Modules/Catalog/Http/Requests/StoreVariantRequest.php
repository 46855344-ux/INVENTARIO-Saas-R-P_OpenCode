<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_variants', 'sku')->where('company_id', $companyId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('product_variants', 'barcode')->where('company_id', $companyId),
            ],
            'attributes' => ['sometimes', 'nullable', 'array'],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la variante es obligatorio.',
            'name.max' => 'El nombre no puede superar los 150 caracteres.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.max' => 'El SKU no puede superar los 50 caracteres.',
            'sku.unique' => 'Ya existe una variante con ese SKU en la empresa.',
            'barcode.max' => 'El código de barras no puede superar los 50 caracteres.',
            'barcode.unique' => 'Ya existe una variante con ese código de barras en la empresa.',
            'cost.numeric' => 'El costo debe ser un número.',
            'cost.min' => 'El costo no puede ser negativo.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}
