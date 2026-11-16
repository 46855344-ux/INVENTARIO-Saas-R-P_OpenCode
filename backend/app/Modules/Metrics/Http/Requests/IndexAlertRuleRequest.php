<?php

namespace App\Modules\Metrics\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexAlertRuleRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
            'type' => ['sometimes', 'nullable', 'in:min_stock,inactivity'],
        ]);
    }
}
