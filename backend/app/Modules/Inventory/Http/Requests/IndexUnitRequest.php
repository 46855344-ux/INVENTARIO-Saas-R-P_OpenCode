<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexUnitRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
            'dimension' => ['sometimes', 'nullable', 'in:mass,volume,count,length,time,other'],
        ]);
    }
}
