<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexMovementRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'type' => ['sometimes', 'nullable', 'in:entry,exit,return,adjustment,transfer_in,transfer_out,reversal'],
            'status' => ['sometimes', 'nullable', 'in:confirmed'],
        ]);
    }
}
