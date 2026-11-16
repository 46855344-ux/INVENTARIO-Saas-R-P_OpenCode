<?php

namespace App\Modules\Audit\Http\Resources;

use App\Modules\Audit\Models\StockCountItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockCountItem */
class StockCountItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant_id' => $this->variant_id,
            'theoretical_quantity' => $this->theoretical_quantity,
            'counted_quantity' => $this->counted_quantity,
            'difference' => $this->difference,
            'variant' => $this->whenLoaded('variant', fn (): ?array => $this->variant ? [
                'id' => $this->variant->id,
                'sku' => $this->variant->sku,
                'name' => $this->variant->name,
            ] : null, null),
        ];
    }
}
