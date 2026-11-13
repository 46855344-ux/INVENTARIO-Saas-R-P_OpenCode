<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexConversionRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
        ]);
    }
}
