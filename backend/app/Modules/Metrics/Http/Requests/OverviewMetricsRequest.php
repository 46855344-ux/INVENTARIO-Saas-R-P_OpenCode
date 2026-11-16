<?php

namespace App\Modules\Metrics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OverviewMetricsRequest extends FormRequest
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
            'location_id' => ['sometimes', 'nullable', 'integer'],
            'days' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ];
    }
}
