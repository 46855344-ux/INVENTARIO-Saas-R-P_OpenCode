<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexStockRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'variant_id' => ['sometimes', 'nullable', 'integer', 'exists:product_variants,id'],
            'location_id' => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
        ]);
    }
}
