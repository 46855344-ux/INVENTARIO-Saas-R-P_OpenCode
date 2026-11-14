<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\Location;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\TransferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Traslado atómico de existencias entre dos ubicaciones de la misma empresa.
 */
#[Fillable([
    'company_id',
    'from_location_id',
    'to_location_id',
    'status',
    'user_id',
    'confirmed_at',
    'idempotency_key',
    'notes',
])]
class Transfer extends Model
{
    /** @use HasFactory<TransferFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_CONFIRMED = 'confirmed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TransferItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    protected static function newFactory(): TransferFactory
    {
        return TransferFactory::new();
    }
}
