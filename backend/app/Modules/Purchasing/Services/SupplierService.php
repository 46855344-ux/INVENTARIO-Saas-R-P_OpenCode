<?php

namespace App\Modules\Purchasing\Services;

use App\Modules\Identity\Services\TenantContext;
use App\Modules\Purchasing\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class SupplierService
{
    /**
     * @param  array{search?: string|null, status?: string|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Supplier::query()
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, tax_id?: string|null, email?: string|null, phone?: string|null}  $data
     */
    public function create(TenantContext $context, array $data): Supplier
    {
        return Supplier::query()->create([
            'company_id' => $context->company->id,
            'name' => $data['name'],
            'tax_id' => $data['tax_id'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => Supplier::STATUS_ACTIVE,
        ]);
    }

    public function find(TenantContext $context, int $supplierId): Supplier
    {
        $supplier = Supplier::query()
            ->where('company_id', $context->company->id)
            ->whereKey($supplierId)
            ->first();

        if ($supplier === null) {
            throw (new ModelNotFoundException)->setModel(Supplier::class, [$supplierId]);
        }

        return $supplier;
    }

    /**
     * @param  array{name?: string, tax_id?: string|null, email?: string|null, phone?: string|null}  $data
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $attributes = Arr::only($data, ['name', 'tax_id', 'email', 'phone']);

        if ($attributes !== []) {
            $supplier->update($attributes);
        }

        return $supplier->fresh();
    }

    public function changeStatus(Supplier $supplier, string $status): Supplier
    {
        $supplier->update(['status' => $status]);

        return $supplier->fresh();
    }
}
