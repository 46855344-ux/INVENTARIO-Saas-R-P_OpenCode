<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Almacén o ubicación física de una empresa. Un almacén es una ubicación de
 * tipo `warehouse`; las demás cuelgan de un padre.
 */
#[Fillable(['company_id', 'parent_id', 'code', 'name', 'type', 'status'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /** @var list<string> */
    public const TYPES = ['warehouse', 'zone', 'shelf', 'bin', 'other'];

    /**
     * @return BelongsTo<Location, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Location, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): LocationFactory
    {
        return LocationFactory::new();
    }
}
