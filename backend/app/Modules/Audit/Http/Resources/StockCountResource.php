<?php

namespace App\Modules\Audit\Http\Resources;

use App\Modules\Audit\Models\StockCount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockCount */
class StockCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'notes' => $this->notes,
            'reason' => $this->reason,
            'counted_at' => $this->counted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'location' => $this->whenLoaded('location', fn (): ?array => $this->location ? [
                'id' => $this->location->id,
                'code' => $this->location->code,
                'name' => $this->location->name,
            ] : null, null),
            'user' => $this->whenLoaded('user', fn (): ?array => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null, null),
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn (): ?array => $this->reviewedBy ? ['id' => $this->reviewedBy->id, 'name' => $this->reviewedBy->name] : null, null),
            'approved_by' => $this->whenLoaded('approvedBy', fn (): ?array => $this->approvedBy ? ['id' => $this->approvedBy->id, 'name' => $this->approvedBy->name] : null, null),
            'items' => $this->whenLoaded('items', fn () => StockCountItemResource::collection($this->items)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
