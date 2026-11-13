<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Unidad de medida de una empresa.
 */
#[Fillable(['company_id', 'code', 'name', 'dimension', 'precision', 'status'])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /** @var list<string> */
    public const DIMENSIONS = ['mass', 'volume', 'count', 'length', 'time', 'other'];

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): UnitFactory
    {
        return UnitFactory::new();
    }
}
