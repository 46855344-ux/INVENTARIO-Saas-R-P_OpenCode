<?php

namespace App\Modules\Purchasing\Http\Resources;

use App\Modules\Purchasing\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transfer
 */
class TransferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'notes' => $this->notes,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'from_location' => $this->whenLoaded('fromLocation', fn (): ?array => $this->fromLocation ? [
                'id' => $this->fromLocation->id,
                'code' => $this->fromLocation->code,
                'name' => $this->fromLocation->name,
            ] : null, null),
            'to_location' => $this->whenLoaded('toLocation', fn (): ?array => $this->toLocation ? [
                'id' => $this->toLocation->id,
                'code' => $this->toLocation->code,
                'name' => $this->toLocation->name,
            ] : null, null),
            'user' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null, null),
            'items' => $this->whenLoaded(
                'items',
                fn () => TransferItemResource::collection($this->items),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
