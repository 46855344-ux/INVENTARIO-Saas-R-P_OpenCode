<?php

namespace App\Modules\Inventory\Http\Resources;

use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockMovement
 */
class StockMovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'reason' => $this->reason,
            'document_reference' => $this->document_reference,
            'original_movement_id' => $this->original_movement_id,
            'reversal_of_movement_id' => $this->reversal_of_movement_id,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null, null),
            'lines' => $this->whenLoaded(
                'lines',
                fn () => StockMovementLineResource::collection($this->lines),
            ),
            'reversal' => $this->whenLoaded(
                'reversal',
                fn (): ?array => $this->reversal ? [
                    'id' => $this->reversal->id,
                    'type' => $this->reversal->type,
                    'confirmed_at' => $this->reversal->confirmed_at?->toIso8601String(),
                ] : null,
                null,
            ),
        ];
    }
}
