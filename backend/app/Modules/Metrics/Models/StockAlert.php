<?php

namespace App\Modules\Metrics\Models;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Inventory\Models\Location;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\StockAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'alert_rule_id', 'type', 'variant_id', 'location_id', 'message', 'status', 'detected_at', 'resolved_at', 'dedup_key'])]
class StockAlert extends Model
{
    /** @use HasFactory<StockAlertFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_RESOLVED = 'resolved';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AlertRule, $this>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'alert_rule_id');
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

    protected static function newFactory(): StockAlertFactory
    {
        return StockAlertFactory::new();
    }
}
