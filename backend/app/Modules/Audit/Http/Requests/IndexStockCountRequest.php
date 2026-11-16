<?php

namespace App\Modules\Audit\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexStockCountRequest extends PaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', 'nullable', 'in:open,reviewing,approved'],
            'location_id' => ['sometimes', 'nullable', 'integer'],
        ]);
    }
}
