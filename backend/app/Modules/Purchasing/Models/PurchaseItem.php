<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Inventory\Models\Location;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de compra con costo histórico congelado.
 */
#[Fillable([
    'company_id',
    'purchase_id',
    'variant_id',
    'location_id',
    'quantity',
    'unit_cost',
    'subtotal',
])]
class PurchaseItem extends Model
{
    /** @use HasFactory<PurchaseItemFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Purchase, $this>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected static function newFactory(): PurchaseItemFactory
    {
        return PurchaseItemFactory::new();
    }
}
