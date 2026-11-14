<?php

namespace App\Modules\Purchasing\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
        $supplierId = (int) $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'tax_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                Rule::unique('suppliers', 'tax_id')
                    ->where('company_id', $companyId)
                    ->ignore($supplierId),
            ],
            'email' => ['sometimes', 'nullable', 'email', 'max:150'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'name.max' => 'El nombre no puede superar los 150 caracteres.',
            'tax_id.unique' => 'Ya existe un proveedor con ese documento en la empresa.',
            'tax_id.max' => 'El documento no puede superar los 30 caracteres.',
            'email.email' => 'El correo del proveedor no es válido.',
            'email.max' => 'El correo no puede superar los 150 caracteres.',
            'phone.max' => 'El teléfono no puede superar los 50 caracteres.',
        ];
    }
}
