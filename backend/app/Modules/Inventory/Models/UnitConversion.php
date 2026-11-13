<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\UnitConversionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Factor de conversión entre dos unidades de la misma empresa y dimensión.
 */
#[Fillable(['company_id', 'from_unit_id', 'to_unit_id', 'factor', 'status'])]
class UnitConversion extends Model
{
    /** @use HasFactory<UnitConversionFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'factor' => 'decimal:6',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): UnitConversionFactory
    {
        return UnitConversionFactory::new();
    }
}
