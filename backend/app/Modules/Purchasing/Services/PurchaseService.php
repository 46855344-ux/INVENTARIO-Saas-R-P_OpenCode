<?php

namespace App\Modules\Purchasing\Services;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Services\InventoryMovementService;
use App\Modules\Purchasing\Exceptions\IdempotencyConflictException;
use App\Modules\Purchasing\Exceptions\PurchaseNotEditableException;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Models\PurchaseItem;
use App\Modules\Purchasing\Models\Supplier;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(private readonly InventoryMovementService $inventory) {}

    /**
     * @param  array{search?: string|null, status?: string|null, supplier_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Purchase::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('document_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['supplier_id']), fn ($query) => $query->where('supplier_id', $filters['supplier_id']))
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    public function find(TenantContext $context, int $purchaseId): Purchase
    {
        $purchase = Purchase::query()
            ->with($this->relations())
            ->where('company_id', $context->company->id)
            ->whereKey($purchaseId)
            ->first();

        if ($purchase === null) {
            throw (new ModelNotFoundException)->setModel(Purchase::class, [$purchaseId]);
        }

        return $purchase;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(TenantContext $context, User $user, array $data): Purchase
    {
        return DB::transaction(function () use ($context, $user, $data): Purchase {
            $supplier = $this->resolveSupplier($context, (int) $data['supplier_id']);

            $purchase = Purchase::query()->create([
                'company_id' => $context->company->id,
                'supplier_id' => $supplier->id,
                'document_number' => $data['document_number'] ?? null,
                'ordered_at' => $data['ordered_at'],
                'status' => Purchase::STATUS_DRAFT,
                'total' => '0.00',
                'user_id' => $user->id,
                'received_by' => null,
                'received_at' => null,
                'reception_idempotency_key' => null,
                'notes' => $data['notes'] ?? null,
            ]);

            $total = $this->replaceItems($context, $purchase, $data['items']);
            $purchase->update(['total' => (string) $total]);

            return $purchase->load($this->relations());
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TenantContext $context, Purchase $purchase, array $data): Purchase
    {
        if (! $purchase->isDraft()) {
            throw new PurchaseNotEditableException;
        }

        return DB::transaction(function () use ($context, $purchase, $data): Purchase {
            $supplier = $this->resolveSupplier($context, (int) $data['supplier_id']);

            $purchase->update([
                'supplier_id' => $supplier->id,
                'document_number' => $data['document_number'] ?? null,
                'ordered_at' => $data['ordered_at'],
                'notes' => $data['notes'] ?? null,
            ]);

            $total = $this->replaceItems($context, $purchase, $data['items']);
            $purchase->update(['total' => (string) $total]);

            return $purchase->fresh($this->relations());
        });
    }

    public function receive(TenantContext $context, User $user, Purchase $purchase, string $key): Purchase
    {
        if ($purchase->isReceived()) {
            return $purchase->load($this->relations());
        }

        $this->assertKeyAvailable($context, $purchase, $key);

        try {
            return DB::transaction(function () use ($context, $user, $purchase, $key): Purchase {
                $locked = Purchase::query()
                    ->where('company_id', $context->company->id)
                    ->whereKey($purchase->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->isReceived()) {
                    return $locked->load($this->relations());
                }

                $locked->loadMissing('items');

                foreach ($locked->items()->orderBy('id')->get() as $item) {
                    $this->inventory->record($context, $user, StockMovement::TYPE_ENTRY, [
                        'variant_id' => $item->variant_id,
                        'location_id' => $item->location_id,
                        'quantity' => $item->quantity,
                        'reason' => "Recepción de compra #{$locked->id}",
                        'document_reference' => $locked->document_number,
                        'reference_type' => 'purchase',
                        'reference_id' => $locked->id,
                        'idempotency_key' => "purchase:{$locked->id}:item:{$item->id}",
                    ]);
                }

                $locked->update([
                    'status' => Purchase::STATUS_RECEIVED,
                    'received_by' => $user->id,
                    'received_at' => now(),
                    'reception_idempotency_key' => $key,
                ]);

                return $locked->fresh($this->relations());
            });
        } catch (QueryException $exception) {
            $existing = Purchase::query()
                ->where('company_id', $context->company->id)
                ->where('reception_idempotency_key', $key)
                ->first();

            if ($existing !== null && $existing->id === $purchase->id) {
                return $existing->load($this->relations());
            }

            if ($existing !== null) {
                throw new IdempotencyConflictException;
            }

            throw $exception;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function replaceItems(TenantContext $context, Purchase $purchase, array $items): BigDecimal
    {
        $purchase->items()->delete();

        $total = BigDecimal::of('0')->toScale(2);

        foreach ($items as $item) {
            $variant = $this->resolveVariant($context, (int) $item['variant_id']);
            $location = $this->resolveLocation($context, (int) $item['location_id']);
            $quantity = BigDecimal::of((string) $item['quantity'])->toScale(4, RoundingMode::HalfUp);
            $unitCost = BigDecimal::of((string) $item['unit_cost'])->toScale(4, RoundingMode::HalfUp);
            $subtotal = $quantity->multipliedBy($unitCost)->toScale(2, RoundingMode::HalfUp);

            PurchaseItem::query()->create([
                'company_id' => $context->company->id,
                'purchase_id' => $purchase->id,
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => (string) $quantity,
                'unit_cost' => (string) $unitCost,
                'subtotal' => (string) $subtotal,
            ]);

            $total = $total->plus($subtotal);
        }

        return $total->toScale(2, RoundingMode::HalfUp);
    }

    private function assertKeyAvailable(TenantContext $context, Purchase $purchase, string $key): void
    {
        $usedByAnother = Purchase::query()
            ->where('company_id', $context->company->id)
            ->where('reception_idempotency_key', $key)
            ->whereKeyNot($purchase->id)
            ->exists();

        if ($usedByAnother) {
            throw new IdempotencyConflictException;
        }
    }

    private function resolveSupplier(TenantContext $context, int $supplierId): Supplier
    {
        $supplier = Supplier::query()
            ->where('company_id', $context->company->id)
            ->whereKey($supplierId)
            ->first();

        if ($supplier === null) {
            throw (new ModelNotFoundException)->setModel(Supplier::class, [$supplierId]);
        }

        if (! $supplier->isActive()) {
            throw ValidationException::withMessages([
                'supplier_id' => ['El proveedor está inactivo y no admite compras nuevas.'],
            ]);
        }

        return $supplier;
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
        return ['supplier', 'user', 'receivedBy', 'items.variant', 'items.location'];
    }
}
