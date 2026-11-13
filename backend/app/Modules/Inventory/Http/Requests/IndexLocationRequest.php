<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexLocationRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
            'type' => ['sometimes', 'nullable', 'in:warehouse,zone,shelf,bin,other'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
        ]);
    }
}
