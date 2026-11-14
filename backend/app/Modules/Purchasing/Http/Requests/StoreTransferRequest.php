<?php

namespace App\Modules\Purchasing\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
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
            'from_location_id' => ['required', 'integer', $locationExists],
            'to_location_id' => ['required', 'integer', 'different:from_location_id', $locationExists],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'distinct', $variantExists],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_location_id.required' => 'La ubicación de origen es obligatoria.',
            'from_location_id.exists' => 'La ubicación de origen no pertenece a la empresa activa.',
            'to_location_id.required' => 'La ubicación de destino es obligatoria.',
            'to_location_id.different' => 'El origen y el destino deben ser distintos.',
            'to_location_id.exists' => 'La ubicación de destino no pertenece a la empresa activa.',
            'notes.max' => 'Las notas no pueden superar los 255 caracteres.',
            'items.required' => 'El traslado debe tener al menos una línea.',
            'items.min' => 'El traslado debe tener al menos una línea.',
            'items.*.variant_id.required' => 'La variante es obligatoria.',
            'items.*.variant_id.distinct' => 'No se puede repetir la misma variante en el traslado.',
            'items.*.variant_id.exists' => 'La variante no pertenece a la empresa activa.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.gt' => 'La cantidad debe ser mayor que cero.',
            'idempotency_key.required' => 'La cabecera Idempotency-Key es obligatoria.',
        ];
    }

    public function idempotencyKey(): string
    {
        return (string) $this->validated('idempotency_key');
    }
}
