<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LocationService
{
    /**
     * @param  array{search?: string|null, status?: string|null, type?: string|null, parent_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Location::query()
            ->with('parent')
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(isset($filters['parent_id']), fn ($query) => $query->where('parent_id', $filters['parent_id']))
            ->orderBy('code')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{code: string, name: string, type: string, parent_id?: int|null}  $data
     */
    public function create(TenantContext $context, array $data): Location
    {
        $location = Location::query()->create([
            'company_id' => $context->company->id,
            'parent_id' => $data['parent_id'] ?? null,
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'status' => Location::STATUS_ACTIVE,
        ]);

        return $location->load('parent');
    }

    public function find(TenantContext $context, int $locationId): Location
    {
        $location = Location::query()
            ->with('parent')
            ->where('company_id', $context->company->id)
            ->whereKey($locationId)
            ->first();

        if ($location === null) {
            throw (new ModelNotFoundException)->setModel(Location::class, [$locationId]);
        }

        return $location;
    }

    /**
     * @param  array{code?: string, name?: string, type?: string, parent_id?: int|null}  $data
     */
    public function update(Location $location, array $data): Location
    {
        $attributes = Arr::only($data, ['code', 'name', 'type', 'parent_id']);

        if (array_key_exists('parent_id', $data) && $data['parent_id'] === null) {
            $attributes['parent_id'] = null;
        }

        if ($attributes !== []) {
            $location->update($attributes);
        }

        return $location->fresh('parent');
    }

    public function changeStatus(Location $location, string $status): Location
    {
        if ($status === Location::STATUS_INACTIVE) {
            $this->assertCanDeactivate($location);
        }

        $location->update(['status' => $status]);

        return $location->fresh('parent');
    }

    /**
     * Guardia preparada para Sprint 4: no desactivar una ubicación con stock.
     */
    private function assertCanDeactivate(Location $location): void
    {
        if (! Schema::hasTable('stock_balances')) {
            return;
        }

        $hasStock = DB::table('stock_balances')
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'status' => ['La ubicación tiene existencias; realice un traslado antes de desactivarla.'],
            ]);
        }
    }
}
