<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Proveedor de una empresa. Desactivarlo no elimina sus compras.
 */
#[Fillable(['company_id', 'name', 'tax_id', 'email', 'phone', 'status'])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }
}
