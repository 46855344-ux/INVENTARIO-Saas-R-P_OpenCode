<?php

namespace App\Modules\Metrics\Http\Requests;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlertRuleRequest extends FormRequest
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
        $ruleId = (int) $this->route('id');

        return [
            'type' => ['required', 'in:min_stock,inactivity'],
            'variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')->where('company_id', $companyId),
                Rule::unique('alert_rules')->where('company_id', $companyId)
                    ->where('type', $this->input('type'))
                    ->where('variant_id', $this->input('variant_id'))
                    ->where('location_id', $this->input('location_id'))
                    ->ignore($ruleId),
            ],
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where('company_id', $companyId),
            ],
            'threshold' => ['required_if:type,min_stock', 'nullable', 'numeric', 'min:0'],
            'inactivity_days' => ['required_if:type,inactivity', 'nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de regla es obligatorio.',
            'type.in' => 'El tipo de regla no es válido.',
            'variant_id.required' => 'La variante es obligatoria.',
            'variant_id.exists' => 'La variante no pertenece a la empresa activa.',
            'variant_id.unique' => 'Ya existe otra regla para esa variante y ubicación.',
            'location_id.required' => 'La ubicación es obligatoria.',
            'location_id.exists' => 'La ubicación no pertenece a la empresa activa.',
            'threshold.required_if' => 'El umbral es obligatorio para stock mínimo.',
            'threshold.min' => 'El umbral no puede ser negativo.',
            'inactivity_days.required_if' => 'Los días de inactividad son obligatorios.',
            'inactivity_days.min' => 'Los días de inactividad deben ser al menos 1.',
            'inactivity_days.max' => 'Los días de inactividad no pueden superar 365.',
        ];
    }
}
