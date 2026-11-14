<?php

namespace App\Modules\Purchasing\Http\Resources;

use App\Modules\Purchasing\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Purchase
 */
class PurchaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'document_number' => $this->document_number,
            'ordered_at' => $this->ordered_at?->toDateString(),
            'notes' => $this->notes,
            'total' => $this->total,
            'received_at' => $this->received_at?->toIso8601String(),
            'supplier' => $this->whenLoaded('supplier', fn (): ?array => $this->supplier ? [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
                'tax_id' => $this->supplier->tax_id,
            ] : null, null),
            'user' => $this->whenLoaded('user', fn (): ?array => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null, null),
            'received_by' => $this->whenLoaded('receivedBy', fn (): ?array => $this->receivedBy ? [
                'id' => $this->receivedBy->id,
                'name' => $this->receivedBy->name,
            ] : null, null),
            'items' => $this->whenLoaded(
                'items',
                fn () => PurchaseItemResource::collection($this->items),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
