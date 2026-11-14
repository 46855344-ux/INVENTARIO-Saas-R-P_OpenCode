<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Exceptions\MovementAlreadyReversedException;
use App\Modules\Inventory\Exceptions\MovementNotReversibleException;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockMovementLine;
use App\Modules\Inventory\Models\Unit;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Única puerta de mutación de existencias. Toda operación se ejecuta en una
 * transacción y deja un movimiento histórico.
 */
class InventoryMovementService
{
    /**
     * @param  array{search?: string|null, variant_id?: int|null, location_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginateStock(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return StockBalance::query()
            ->with(['variant.product', 'location'])
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->whereHas('variant', function ($variant) use ($search): void {
                        $variant->where('sku', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhereHas('product', fn ($product) => $product->where('name', 'like', "%{$search}%"));
                    })->orWhereHas('location', function ($location) use ($search): void {
                        $location->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when(isset($filters['variant_id']), fn ($query) => $query->where('variant_id', $filters['variant_id']))
            ->when(isset($filters['location_id']), fn ($query) => $query->where('location_id', $filters['location_id']))
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{type?: string|null, status?: string|null, per_page?: int|null}  $filters
     */
    public function paginateMovements(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with($this->movementRelations())
            ->where('company_id', $context->company->id)
            ->when(isset($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    public function findMovement(TenantContext $context, int $movementId): StockMovement
    {
        $movement = StockMovement::query()
            ->with($this->movementRelations())
            ->where('company_id', $context->company->id)
            ->whereKey($movementId)
            ->first();

        if ($movement === null) {
            throw (new ModelNotFoundException)->setModel(StockMovement::class, [$movementId]);
        }

        return $movement;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(TenantContext $context, User $user, string $type, array $data): StockMovement
    {
        $key = (string) ($data['idempotency_key'] ?? '');

        $existing = $this->findByIdempotencyKey($context, $key);
        if ($existing !== null) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($context, $user, $type, $data, $key): StockMovement {
                $variant = $this->resolveVariant($context, (int) $data['variant_id']);
                $location = $this->resolveLocation($context, (int) $data['location_id']);
                $unit = ! empty($data['unit_id']) ? $this->resolveUnit($context, (int) $data['unit_id']) : null;
                $quantity = BigDecimal::of((string) $data['quantity'])->toScale(4, RoundingMode::HalfUp);
                $direction = $this->directionFor($type, $data);

                $balance = $this->lockBalance($context, $variant, $location);
                $newQuantity = $this->applyDirection($balance->quantity, $quantity, $direction);
                $balance->update(['quantity' => (string) $newQuantity]);

                $movement = StockMovement::query()->create([
                    'company_id' => $context->company->id,
                    'type' => $type,
                    'status' => StockMovement::STATUS_CONFIRMED,
                    'user_id' => $user->id,
                    'reason' => $data['reason'],
                    'document_reference' => $data['document_reference'] ?? null,
                    'original_movement_id' => $data['original_movement_id'] ?? null,
                    'reversal_of_movement_id' => null,
                    'idempotency_key' => $key !== '' ? $key : null,
                    'confirmed_at' => now(),
                ]);

                StockMovementLine::query()->create([
                    'company_id' => $context->company->id,
                    'stock_movement_id' => $movement->id,
                    'variant_id' => $variant->id,
                    'location_id' => $location->id,
                    'unit_id' => $unit?->id,
                    'quantity' => (string) $quantity,
                    'normalized_quantity' => (string) $quantity,
                    'direction' => $direction,
                ]);

                return $movement->load($this->movementRelations());
            });
        } catch (QueryException $exception) {
            $existing = $this->findByIdempotencyKey($context, $key);
            if ($existing !== null) {
                return $existing;
            }

            throw $exception;
        }
    }

    public function reverse(TenantContext $context, User $user, StockMovement $original, string $reason): StockMovement
    {
        if ($original->isReversal()) {
            throw new MovementNotReversibleException;
        }

        if (StockMovement::query()->where('reversal_of_movement_id', $original->id)->exists()) {
            throw new MovementAlreadyReversedException;
        }

        try {
            return DB::transaction(function () use ($context, $user, $original, $reason): StockMovement {
                $original->loadMissing(['lines']);

                $movement = StockMovement::query()->create([
                    'company_id' => $context->company->id,
                    'type' => StockMovement::TYPE_REVERSAL,
                    'status' => StockMovement::STATUS_CONFIRMED,
                    'user_id' => $user->id,
                    'reason' => $reason,
                    'document_reference' => null,
                    'original_movement_id' => null,
                    'reversal_of_movement_id' => $original->id,
                    'idempotency_key' => null,
                    'confirmed_at' => now(),
                ]);

                foreach ($original->lines as $line) {
                    $direction = $line->direction === StockMovementLine::DIRECTION_IN
                        ? StockMovementLine::DIRECTION_OUT
                        : StockMovementLine::DIRECTION_IN;
                    $variant = $this->resolveVariant($context, (int) $line->variant_id);
                    $location = $this->resolveLocation($context, (int) $line->location_id);
                    $quantity = BigDecimal::of((string) $line->quantity)->toScale(4, RoundingMode::HalfUp);

                    $balance = $this->lockBalance($context, $variant, $location);
                    $newQuantity = $this->applyDirection($balance->quantity, $quantity, $direction);
                    $balance->update(['quantity' => (string) $newQuantity]);

                    StockMovementLine::query()->create([
                        'company_id' => $context->company->id,
                        'stock_movement_id' => $movement->id,
                        'variant_id' => $variant->id,
                        'location_id' => $location->id,
                        'unit_id' => $line->unit_id,
                        'quantity' => (string) $quantity,
                        'normalized_quantity' => (string) $quantity,
                        'direction' => $direction,
                    ]);
                }

                return $movement->load($this->movementRelations());
            });
        } catch (QueryException $exception) {
            if (StockMovement::query()->where('reversal_of_movement_id', $original->id)->exists()) {
                throw new MovementAlreadyReversedException;
            }

            throw $exception;
        }
    }

    private function findByIdempotencyKey(TenantContext $context, string $key): ?StockMovement
    {
        if ($key === '') {
            return null;
        }

        return StockMovement::query()
            ->with($this->movementRelations())
            ->where('company_id', $context->company->id)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function directionFor(string $type, array $data): string
    {
        return match ($type) {
            StockMovement::TYPE_EXIT => StockMovementLine::DIRECTION_OUT,
            StockMovement::TYPE_ADJUSTMENT => $data['direction'] ?? StockMovementLine::DIRECTION_IN,
            default => StockMovementLine::DIRECTION_IN,
        };
    }

    private function lockBalance(TenantContext $context, ProductVariant $variant, Location $location): StockBalance
    {
        $balance = StockBalance::query()
            ->where('company_id', $context->company->id)
            ->where('variant_id', $variant->id)
            ->where('location_id', $location->id)
            ->lockForUpdate()
            ->first();

        if ($balance === null) {
            try {
                $balance = StockBalance::query()->create([
                    'company_id' => $context->company->id,
                    'variant_id' => $variant->id,
                    'location_id' => $location->id,
                    'quantity' => '0.0000',
                    'reserved_quantity' => '0.0000',
                ]);
            } catch (QueryException) {
                $balance = StockBalance::query()
                    ->where('company_id', $context->company->id)
                    ->where('variant_id', $variant->id)
                    ->where('location_id', $location->id)
                    ->firstOrFail();
            }
        }

        return StockBalance::query()->whereKey($balance->id)->lockForUpdate()->firstOrFail();
    }

    private function applyDirection(string $current, BigDecimal $quantity, string $direction): BigDecimal
    {
        $balance = BigDecimal::of($current);

        if ($direction === StockMovementLine::DIRECTION_OUT) {
            $newQuantity = $balance->minus($quantity);

            if ($newQuantity->isNegative()) {
                throw new InsufficientStockException;
            }

            return $newQuantity;
        }

        return $balance->plus($quantity);
    }

    private function resolveVariant(TenantContext $context, int $variantId): ProductVariant
    {
        $variant = ProductVariant::query()
            ->where('company_id', $context->company->id)
            ->whereKey($variantId)
            ->first();

        if ($variant === null) {
            throw (new ModelNotFoundException)->setModel(ProductVariant::class, [$variantId]);
        }

        return $variant;
    }

    private function resolveLocation(TenantContext $context, int $locationId): Location
    {
        $location = Location::query()
            ->where('company_id', $context->company->id)
            ->whereKey($locationId)
            ->first();

        if ($location === null) {
            throw (new ModelNotFoundException)->setModel(Location::class, [$locationId]);
        }

        return $location;
    }

    private function resolveUnit(TenantContext $context, int $unitId): Unit
    {
        $unit = Unit::query()
            ->where('company_id', $context->company->id)
            ->whereKey($unitId)
            ->first();

        if ($unit === null) {
            throw (new ModelNotFoundException)->setModel(Unit::class, [$unitId]);
        }

        return $unit;
    }

    /**
     * @return array<int, string>
     */
    private function movementRelations(): array
    {
        return ['user', 'lines.variant', 'lines.location', 'lines.unit', 'reversal'];
    }
}
