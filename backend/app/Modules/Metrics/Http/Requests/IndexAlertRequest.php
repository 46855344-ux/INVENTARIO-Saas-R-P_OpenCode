<?php

namespace App\Modules\Metrics\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexAlertRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', 'nullable', 'in:open,resolved'],
            'type' => ['sometimes', 'nullable', 'in:min_stock,inactivity'],
        ]);
    }
}
