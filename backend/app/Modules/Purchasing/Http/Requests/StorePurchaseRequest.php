<?php

namespace App\Modules\Purchasing\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
        $locationExists = Rule::exists('locations', 'id')
            ->where(fn ($query) => $query->where('company_id', $companyId));
        $variantExists = Rule::exists('product_variants', 'id')
            ->where(fn ($query) => $query->where('company_id', $companyId));

        return [
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->where('company_id', $companyId),
            ],
            'ordered_at' => ['required', 'date'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'distinct', $variantExists],
            'items.*.location_id' => ['required', 'integer', $locationExists],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'El proveedor es obligatorio.',
            'supplier_id.exists' => 'El proveedor no pertenece a la empresa activa.',
            'ordered_at.required' => 'La fecha de la compra es obligatoria.',
            'ordered_at.date' => 'La fecha de la compra no es válida.',
            'document_number.max' => 'El número de documento no puede superar los 50 caracteres.',
            'notes.max' => 'Las notas no pueden superar los 255 caracteres.',
            'items.required' => 'La compra debe tener al menos una línea.',
            'items.min' => 'La compra debe tener al menos una línea.',
            'items.*.variant_id.required' => 'La variante es obligatoria.',
            'items.*.variant_id.distinct' => 'No se puede repetir la misma variante en la compra.',
            'items.*.variant_id.exists' => 'La variante no pertenece a la empresa activa.',
            'items.*.location_id.required' => 'La ubicación de destino es obligatoria.',
            'items.*.location_id.exists' => 'La ubicación no pertenece a la empresa activa.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.gt' => 'La cantidad debe ser mayor que cero.',
            'items.*.unit_cost.required' => 'El costo unitario es obligatorio.',
            'items.*.unit_cost.min' => 'El costo unitario no puede ser negativo.',
        ];
    }
}
