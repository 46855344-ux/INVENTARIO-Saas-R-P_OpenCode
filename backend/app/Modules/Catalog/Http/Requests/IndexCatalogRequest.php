<?php

namespace App\Modules\Catalog\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexCatalogRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
        ]);
    }
}
