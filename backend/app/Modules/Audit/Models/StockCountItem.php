<?php

namespace App\Modules\Audit\Models;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\StockCountItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'stock_count_id', 'variant_id', 'theoretical_quantity', 'counted_quantity', 'difference'])]
class StockCountItem extends Model
{
    /** @use HasFactory<StockCountItemFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theoretical_quantity' => 'decimal:4',
            'counted_quantity' => 'decimal:4',
            'difference' => 'decimal:4',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function count(): BelongsTo
    {
        return $this->belongsTo(StockCount::class, 'stock_count_id');
    }

    protected static function newFactory(): StockCountItemFactory
    {
        return StockCountItemFactory::new();
    }
}
