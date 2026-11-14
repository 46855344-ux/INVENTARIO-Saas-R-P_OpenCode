<?php

namespace App\Modules\Inventory\Http\Resources;

use App\Modules\Inventory\Models\StockBalance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockBalance
 */
class StockBalanceResource extends JsonResource
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
            'reserved_quantity' => $this->reserved_quantity,
            'variant' => $this->whenLoaded(
                'variant',
                fn (): ?array => $this->variant ? [
                    'id' => $this->variant->id,
                    'sku' => $this->variant->sku,
                    'name' => $this->variant->name,
                    'product' => $this->variant->product ? [
                        'id' => $this->variant->product->id,
                        'name' => $this->variant->product->name,
                    ] : null,
                ] : null,
                null,
            ),
            'location' => $this->whenLoaded(
                'location',
                fn (): ?array => $this->location ? [
                    'id' => $this->location->id,
                    'code' => $this->location->code,
                    'name' => $this->location->name,
                ] : null,
                null,
            ),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
