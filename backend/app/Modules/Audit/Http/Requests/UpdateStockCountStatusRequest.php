<?php

namespace App\Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockCountStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:reviewing,approved'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser en revisión o aprobado.',
            'reason.required' => 'El motivo es obligatorio.',
        ];
    }
}
