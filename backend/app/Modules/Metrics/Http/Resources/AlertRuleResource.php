<?php

namespace App\Modules\Metrics\Http\Resources;

use App\Modules\Metrics\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AlertRule
 */
class AlertRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'threshold' => $this->threshold,
            'inactivity_days' => $this->inactivity_days,
            'status' => $this->status,
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
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
