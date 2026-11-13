<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Producto del catálogo de una empresa.
 */
#[Fillable(['company_id', 'category_id', 'brand_id', 'name', 'description', 'status'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
