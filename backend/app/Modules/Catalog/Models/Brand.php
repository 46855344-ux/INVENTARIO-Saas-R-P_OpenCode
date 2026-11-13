<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Marca del catálogo de una empresa.
 */
#[Fillable(['company_id', 'name', 'status'])]
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }
}
