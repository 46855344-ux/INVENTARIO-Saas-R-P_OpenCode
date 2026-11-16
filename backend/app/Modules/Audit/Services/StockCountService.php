<?php

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Exceptions\InvalidStockCountStatusException;
use App\Modules\Audit\Models\StockCount;
use App\Modules\Audit\Models\StockCountItem;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockMovementLine;
use App\Modules\Inventory\Services\InventoryMovementService;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockCountService
{
    public function __construct(
        private readonly InventoryMovementService $inventory,
        private readonly AuditLogService $audit,
    ) {}

    /**
     * @param  array{status?: string|null, location_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return StockCount::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['location_id']), fn ($query) => $query->where('location_id', $filters['location_id']))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    public function find(TenantContext $context, int $countId): StockCount
    {
        $count = StockCount::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->whereKey($countId)
            ->first();

        if ($count === null) {
            throw (new ModelNotFoundException)->setModel(StockCount::class, [$countId]);
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(TenantContext $context, User $user, array $data): StockCount
    {
        return DB::transaction(function () use ($context, $user, $data): StockCount {
            $location = Location::query()
                ->where('company_id', $context->company->id)
                ->whereKey((int) $data['location_id'])
                ->firstOrFail();

            $count = StockCount::query()->create([
                'company_id' => $context->company->id,
                'location_id' => $location->id,
                'status' => StockCount::STATUS_OPEN,
                'user_id' => $user->id,
                'counted_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::query()
                    ->where('company_id', $context->company->id)
                    ->whereKey((int) $item['variant_id'])
                    ->firstOrFail();

                $balance = StockBalance::query()
                    ->where('company_id', $context->company->id)
                    ->where('variant_id', $variant->id)
                    ->where('location_id', $location->id)
                    ->first();

                $theoretical = BigDecimal::of((string) ($balance?->quantity ?? '0'))
                    ->toScale(4, RoundingMode::HalfUp);
                $counted = BigDecimal::of((string) $item['counted_quantity'])
                    ->toScale(4, RoundingMode::HalfUp);
                $difference = $counted->minus($theoretical)->toScale(4, RoundingMode::HalfUp);

                StockCountItem::query()->create([
                    'company_id' => $context->company->id,
                    'stock_count_id' => $count->id,
                    'variant_id' => $variant->id,
                    'theoretical_quantity' => (string) $theoretical,
                    'counted_quantity' => (string) $counted,
                    'difference' => (string) $difference,
                ]);
            }

            $this->audit->record($context, $user, 'stock_count.created', 'stock_count', $count->id, [
                'location_id' => $location->id,
                'items' => count($data['items']),
            ]);

            return $count->load($this->relations());
        });
    }

    public function changeStatus(
        TenantContext $context,
        User $user,
        StockCount $count,
        string $status,
        string $reason,
    ): StockCount {
        if ($status === StockCount::STATUS_REVIEWING) {
            if ($count->status !== StockCount::STATUS_OPEN) {
                throw new InvalidStockCountStatusException;
            }

            $count->update([
                'status' => StockCount::STATUS_REVIEWING,
                'reason' => $reason,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            $this->audit->record($context, $user, 'stock_count.reviewing', 'stock_count', $count->id, [
                'reason' => $reason,
            ]);

            return $count->fresh($this->relations());
        }

        if ($count->status !== StockCount::STATUS_REVIEWING) {
            throw new InvalidStockCountStatusException;
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => ['El motivo es obligatorio para aprobar el conteo.'],
            ]);
        }

        return DB::transaction(function () use ($context, $user, $count, $reason): StockCount {
            $locked = StockCount::query()
                ->with('items')
                ->where('company_id', $context->company->id)
                ->whereKey($count->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== StockCount::STATUS_REVIEWING) {
                throw new InvalidStockCountStatusException;
            }

            foreach ($locked->items as $item) {
                $difference = BigDecimal::of((string) $item->difference);

                if ($difference->isZero()) {
                    continue;
                }

                $this->inventory->record($context, $user, StockMovement::TYPE_ADJUSTMENT, [
                    'variant_id' => $item->variant_id,
                    'location_id' => $locked->location_id,
                    'quantity' => (string) $difference->abs(),
                    'direction' => $difference->isPositive()
                        ? StockMovementLine::DIRECTION_IN
                        : StockMovementLine::DIRECTION_OUT,
                    'reason' => "Ajuste por conteo #{$locked->id}: {$reason}",
                    'reference_type' => 'stock_count',
                    'reference_id' => $locked->id,
                    'idempotency_key' => "stock-count:{$locked->id}:item:{$item->id}",
                ]);
            }

            $locked->update([
                'status' => StockCount::STATUS_APPROVED,
                'reason' => $reason,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $this->audit->record($context, $user, 'stock_count.approved', 'stock_count', $locked->id, [
                'reason' => $reason,
                'adjustments' => $locked->items->filter(fn (StockCountItem $item): bool => ! BigDecimal::of((string) $item->difference)->isZero())->count(),
            ]);

            return $locked->fresh($this->relations());
        });
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return ['location', 'user', 'reviewedBy', 'approvedBy', 'items.variant'];
    }
}
