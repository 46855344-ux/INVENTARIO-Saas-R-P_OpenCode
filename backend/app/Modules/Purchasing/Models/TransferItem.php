<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\TransferItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Variante y cantidad incluida en un traslado.
 */
#[Fillable(['company_id', 'transfer_id', 'variant_id', 'quantity'])]
class TransferItem extends Model
{
    /** @use HasFactory<TransferItemFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Transfer, $this>
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    protected static function newFactory(): TransferItemFactory
    {
        return TransferItemFactory::new();
    }
}
