<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Models\SocialIdentity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SocialIdentity
 */
class SocialIdentityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'provider' => $this->provider,
            'label' => SocialIdentity::providerLabel($this->provider),
            'linked_at' => $this->linked_at?->toIso8601String(),
        ];
    }
}
