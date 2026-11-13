<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Variante de un producto, identificada por SKU y código de barras.
 */
#[Fillable(['company_id', 'product_id', 'name', 'sku', 'barcode', 'attributes', 'cost', 'price', 'status'])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'cost' => 'decimal:4',
            'price' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): ProductVariantFactory
    {
        return ProductVariantFactory::new();
    }
}
