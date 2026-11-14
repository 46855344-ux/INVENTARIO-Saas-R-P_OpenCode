<?php

namespace App\Modules\Purchasing\Http\Resources;

use App\Modules\Purchasing\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PurchaseItem
 */
class PurchaseItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant_id' => $this->variant_id,
            'location_id' => $this->location_id,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'subtotal' => $this->subtotal,
            'variant' => $this->whenLoaded('variant', fn (): ?array => $this->variant ? [
                'id' => $this->variant->id,
                'sku' => $this->variant->sku,
                'name' => $this->variant->name,
            ] : null, null),
            'location' => $this->whenLoaded('location', fn (): ?array => $this->location ? [
                'id' => $this->location->id,
                'code' => $this->location->code,
                'name' => $this->location->name,
            ] : null, null),
        ];
    }
}
