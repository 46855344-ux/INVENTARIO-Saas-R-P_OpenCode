<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_global' => $this->company_id === null,
            'permissions' => $this->whenLoaded(
                'permissions',
                fn (): array => $this->permissions->pluck('code')->values()->all(),
                [],
            ),
        ];
    }
}
