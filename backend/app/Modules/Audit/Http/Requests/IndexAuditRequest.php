<?php

namespace App\Modules\Audit\Http\Requests;

use App\Modules\Shared\Http\Requests\PaginationRequest;

class IndexAuditRequest extends PaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'action' => ['sometimes', 'nullable', 'string', 'max:50'],
            'entity_type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
        ]);
    }
}
