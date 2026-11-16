<?php

namespace App\Modules\Audit\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\Location;
use App\Modules\Shared\Tenancy\BelongsToCompany;
use Database\Factories\StockCountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'location_id', 'status', 'user_id', 'reviewed_by', 'approved_by', 'counted_at', 'reviewed_at', 'approved_at', 'reason', 'notes'])]
class StockCount extends Model
{
    /** @use HasFactory<StockCountFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_APPROVED = 'approved';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'counted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    protected static function newFactory(): StockCountFactory
    {
        return StockCountFactory::new();
    }
}
