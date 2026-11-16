<?php

namespace App\Modules\Metrics\Models;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Inventory\Models\Location;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\AlertRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'type', 'variant_id', 'location_id', 'threshold', 'inactivity_days', 'status'])]
class AlertRule extends Model
{
    /** @use HasFactory<AlertRuleFactory> */
    use BelongsToCompany, HasFactory;

    public const TYPE_MIN_STOCK = 'min_stock';

    public const TYPE_INACTIVITY = 'inactivity';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'threshold' => 'decimal:4',
            'inactivity_days' => 'integer',
        ];
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

    protected static function newFactory(): AlertRuleFactory
    {
        return AlertRuleFactory::new();
    }
}
