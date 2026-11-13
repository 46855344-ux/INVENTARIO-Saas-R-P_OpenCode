<?php

namespace App\Modules\Inventory\Http\Resources;

use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Models\UnitConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UnitConversion
 */
class UnitConversionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_unit_id' => $this->from_unit_id,
            'to_unit_id' => $this->to_unit_id,
            'factor' => $this->factor,
            'status' => $this->status,
            'from_unit' => $this->whenLoaded(
                'fromUnit',
                fn (): ?array => $this->fromUnit ? $this->unitSummary($this->fromUnit) : null,
                null,
            ),
            'to_unit' => $this->whenLoaded(
                'toUnit',
                fn (): ?array => $this->toUnit ? $this->unitSummary($this->toUnit) : null,
                null,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function unitSummary(Unit $unit): array
    {
        return [
            'id' => $unit->id,
            'code' => $unit->code,
            'name' => $unit->name,
            'dimension' => $unit->dimension,
        ];
    }
}
