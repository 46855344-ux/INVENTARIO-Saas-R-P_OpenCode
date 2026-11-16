<?php

namespace App\Modules\Metrics\Http\Resources;

use App\Modules\Metrics\Models\StockAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockAlert
 */
class StockAlertResource extends JsonResource
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
            'message' => $this->message,
            'alert_rule_id' => $this->alert_rule_id,
            'detected_at' => $this->detected_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
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
