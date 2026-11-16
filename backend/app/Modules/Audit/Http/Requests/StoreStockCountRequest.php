<?php

namespace App\Modules\Audit\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = TenantContext::fromRequest($this)->company->id;

        return [
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('company_id', $companyId)],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'distinct', Rule::exists('product_variants', 'id')->where('company_id', $companyId)],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'La ubicación es obligatoria.',
            'location_id.exists' => 'La ubicación no pertenece a la empresa activa.',
            'items.required' => 'El conteo debe tener al menos una línea.',
            'items.*.variant_id.required' => 'La variante es obligatoria.',
            'items.*.variant_id.distinct' => 'No se puede repetir la variante.',
            'items.*.variant_id.exists' => 'La variante no pertenece a la empresa activa.',
            'items.*.counted_quantity.required' => 'La cantidad física es obligatoria.',
            'items.*.counted_quantity.min' => 'La cantidad física no puede ser negativa.',
        ];
    }
}
