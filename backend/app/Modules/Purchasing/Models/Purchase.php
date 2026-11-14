<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Compra a proveedor. Nace en borrador y se vuelve inmutable al recibirse.
 */
#[Fillable([
    'company_id',
    'supplier_id',
    'document_number',
    'ordered_at',
    'status',
    'total',
    'user_id',
    'received_by',
    'received_at',
    'reception_idempotency_key',
    'notes',
])]
class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_RECEIVED = 'received';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ordered_at' => 'date',
            'received_at' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * @return HasMany<PurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    protected static function newFactory(): PurchaseFactory
    {
        return PurchaseFactory::new();
    }
}
