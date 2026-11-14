<?php

namespace App\Modules\Purchasing\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexTransferRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'from_location_id' => ['sometimes', 'nullable', 'integer'],
            'to_location_id' => ['sometimes', 'nullable', 'integer'],
        ]);
    }
}
