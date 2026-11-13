<?php

namespace App\Modules\Catalog\Http\Resources;

use App\Modules\Catalog\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'category' => $this->whenLoaded(
                'category',
                fn (): ?array => $this->category ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ] : null,
                null,
            ),
            'brand' => $this->whenLoaded(
                'brand',
                fn (): ?array => $this->brand ? [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ] : null,
                null,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
