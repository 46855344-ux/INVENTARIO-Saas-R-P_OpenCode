<?php

namespace App\Modules\Purchasing\Http\Resources;

use App\Modules\Purchasing\Models\TransferItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransferItem
 */
class TransferItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant_id' => $this->variant_id,
            'quantity' => $this->quantity,
            'variant' => $this->whenLoaded('variant', fn (): ?array => $this->variant ? [
                'id' => $this->variant->id,
                'sku' => $this->variant->sku,
                'name' => $this->variant->name,
            ] : null, null),
        ];
    }
}
