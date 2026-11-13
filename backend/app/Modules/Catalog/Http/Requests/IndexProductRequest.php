<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexProductRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
        ]);
    }
}
