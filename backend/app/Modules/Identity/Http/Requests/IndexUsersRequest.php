<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexUsersRequest extends PaginationRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:active,inactive'],
            'role_id' => ['sometimes', 'nullable', 'integer', 'exists:roles,id'],
        ]);
    }
}
