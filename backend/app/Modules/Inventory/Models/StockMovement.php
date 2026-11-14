<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Cabecera inmutable de un movimiento de inventario.
 */
#[Fillable(['company_id', 'type', 'status', 'user_id', 'reason', 'document_reference', 'reference_type', 'reference_id', 'original_movement_id', 'reversal_of_movement_id', 'idempotency_key', 'confirmed_at'])]
class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use BelongsToCompany, HasFactory;

    public const TYPE_ENTRY = 'entry';

    public const TYPE_EXIT = 'exit';

    public const TYPE_RETURN = 'return';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_REVERSAL = 'reversal';

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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<StockMovementLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(StockMovementLine::class);
    }

    /**
     * @return BelongsTo<StockMovement, $this>
     */
    public function originalMovement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_movement_id');
    }

    /**
     * @return BelongsTo<StockMovement, $this>
     */
    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_movement_id');
    }

    /**
     * @return HasOne<StockMovement, $this>
     */
    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reversal_of_movement_id');
    }

    public function isReversal(): bool
    {
        return $this->type === self::TYPE_REVERSAL;
    }

    protected static function newFactory(): StockMovementFactory
    {
        return StockMovementFactory::new();
    }
}
