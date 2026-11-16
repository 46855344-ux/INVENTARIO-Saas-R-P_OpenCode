<?php

namespace App\Modules\Metrics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:open,resolved'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser abierto o resuelto.',
        ];
    }
}
