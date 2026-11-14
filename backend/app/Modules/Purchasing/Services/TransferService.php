<?php

namespace App\Modules\Purchasing\Services;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Services\InventoryMovementService;
use App\Modules\Purchasing\Models\Transfer;
use App\Modules\Purchasing\Models\TransferItem;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(private readonly InventoryMovementService $inventory) {}

    /**
     * @param  array{from_location_id?: int|null, to_location_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return Transfer::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->when(isset($filters['from_location_id']), fn ($query) => $query->where('from_location_id', $filters['from_location_id']))
            ->when(isset($filters['to_location_id']), fn ($query) => $query->where('to_location_id', $filters['to_location_id']))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    public function find(TenantContext $context, int $transferId): Transfer
    {
        $transfer = Transfer::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->whereKey($transferId)
            ->first();

        if ($transfer === null) {
            throw (new ModelNotFoundException)->setModel(Transfer::class, [$transferId]);
        }

        return $transfer;
    }

    /**
     * @param  array{from_location_id: int, to_location_id: int, notes?: string|null, items: list<array<string, mixed>>}  $data
     */
    public function execute(TenantContext $context, User $user, array $data, string $key): Transfer
    {
        $existing = $this->findByKey($context, $key);

        if ($existing !== null) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($context, $user, $data, $key): Transfer {
                $from = $this->resolveLocation($context, (int) $data['from_location_id']);
                $to = $this->resolveLocation($context, (int) $data['to_location_id']);

                if ($from->id === $to->id) {
                    throw ValidationException::withMessages([
                        'to_location_id' => ['El origen y el destino deben ser distintos.'],
                    ]);
                }

                $transfer = Transfer::query()->create([
                    'company_id' => $context->company->id,
                    'from_location_id' => $from->id,
                    'to_location_id' => $to->id,
                    'status' => Transfer::STATUS_CONFIRMED,
                    'user_id' => $user->id,
                    'confirmed_at' => now(),
                    'idempotency_key' => $key,
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($data['items'] as $line) {
                    $variant = $this->resolveVariant($context, (int) $line['variant_id']);
                    $quantity = BigDecimal::of((string) $line['quantity'])->toScale(4, RoundingMode::HalfUp);

                    TransferItem::query()->create([
                        'company_id' => $context->company->id,
                        'transfer_id' => $transfer->id,
                        'variant_id' => $variant->id,
                        'quantity' => (string) $quantity,
                    ]);

                    $reason = "Traslado #{$transfer->id} de {$from->code} a {$to->code}";

                    $this->inventory->record($context, $user, StockMovement::TYPE_TRANSFER_OUT, [
                        'variant_id' => $variant->id,
                        'location_id' => $from->id,
                        'quantity' => (string) $quantity,
                        'reason' => $reason,
                        'reference_type' => 'transfer',
                        'reference_id' => $transfer->id,
                        'idempotency_key' => "transfer:{$transfer->id}:out:{$variant->id}",
                    ]);

                    $this->inventory->record($context, $user, StockMovement::TYPE_TRANSFER_IN, [
                        'variant_id' => $variant->id,
                        'location_id' => $to->id,
                        'quantity' => (string) $quantity,
                        'reason' => $reason,
                        'reference_type' => 'transfer',
                        'reference_id' => $transfer->id,
                        'idempotency_key' => "transfer:{$transfer->id}:in:{$variant->id}",
                    ]);
                }

                return $transfer->load($this->relations());
            });
        } catch (QueryException $exception) {
            $existing = $this->findByKey($context, $key);

            if ($existing !== null) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function findByKey(TenantContext $context, string $key): ?Transfer
    {
        if ($key === '') {
            return null;
        }

        return Transfer::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->where('idempotency_key', $key)
            ->first();
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

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return ['fromLocation', 'toLocation', 'user', 'items.variant'];
    }
}
