<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Models\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Usuario dentro de la empresa activa, con su rol y estado de pertenencia.
 *
 * @mixin CompanyUser
 */
class CompanyUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user_id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'status' => $this->status,
            'role' => $this->role ? new RoleResource($this->role) : null,
        ];
    }
}
