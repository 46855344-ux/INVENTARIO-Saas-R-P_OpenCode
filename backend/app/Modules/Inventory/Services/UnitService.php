<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class UnitService
{
    /**
     * @param  array{search?: string|null, status?: string|null, dimension?: string|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Unit::query()
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['dimension']), fn ($query) => $query->where('dimension', $filters['dimension']))
            ->orderBy('code')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{code: string, name: string, dimension: string, precision: int}  $data
     */
    public function create(TenantContext $context, array $data): Unit
    {
        return Unit::query()->create([
            'company_id' => $context->company->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'dimension' => $data['dimension'],
            'precision' => $data['precision'],
            'status' => Unit::STATUS_ACTIVE,
        ]);
    }

    public function find(TenantContext $context, int $unitId): Unit
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
     * @param  array{code?: string, name?: string, dimension?: string, precision?: int}  $data
     */
    public function update(Unit $unit, array $data): Unit
    {
        $attributes = Arr::only($data, ['code', 'name', 'dimension', 'precision']);

        if ($attributes !== []) {
            $unit->update($attributes);
        }

        return $unit->fresh();
    }

    public function changeStatus(Unit $unit, string $status): Unit
    {
        $unit->update(['status' => $status]);

        return $unit->fresh();
    }
}
