<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Reglas del catálogo: categorías y marcas acotadas a la empresa activa.
 */
class CatalogService
{
    /**
     * @param  array{search?: string|null, status?: string|null, per_page?: int|null}  $filters
     */
    public function paginateCategories(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Category::query()
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{name: string}  $data
     */
    public function createCategory(TenantContext $context, array $data): Category
    {
        return Category::query()->create([
            'company_id' => $context->company->id,
            'name' => $data['name'],
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    public function findCategory(TenantContext $context, int $categoryId): Category
    {
        $category = Category::query()
            ->where('company_id', $context->company->id)
            ->whereKey($categoryId)
            ->first();

        if ($category === null) {
            throw (new ModelNotFoundException)->setModel(Category::class, [$categoryId]);
        }

        return $category;
    }

    /**
     * @param  array{name?: string}  $data
     */
    public function updateCategory(Category $category, array $data): Category
    {
        if (isset($data['name'])) {
            $category->update(['name' => $data['name']]);
        }

        return $category->fresh();
    }

    public function changeCategoryStatus(Category $category, string $status): Category
    {
        $category->update(['status' => $status]);

        return $category->fresh();
    }

    /**
     * @param  array{search?: string|null, status?: string|null, per_page?: int|null}  $filters
     */
    public function paginateBrands(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Brand::query()
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{name: string}  $data
     */
    public function createBrand(TenantContext $context, array $data): Brand
    {
        return Brand::query()->create([
            'company_id' => $context->company->id,
            'name' => $data['name'],
            'status' => Brand::STATUS_ACTIVE,
        ]);
    }

    public function findBrand(TenantContext $context, int $brandId): Brand
    {
        $brand = Brand::query()
            ->where('company_id', $context->company->id)
            ->whereKey($brandId)
            ->first();

        if ($brand === null) {
            throw (new ModelNotFoundException)->setModel(Brand::class, [$brandId]);
        }

        return $brand;
    }

    /**
     * @param  array{name?: string}  $data
     */
    public function updateBrand(Brand $brand, array $data): Brand
    {
        if (isset($data['name'])) {
            $brand->update(['name' => $data['name']]);
        }

        return $brand->fresh();
    }

    public function changeBrandStatus(Brand $brand, string $status): Brand
    {
        $brand->update(['status' => $status]);

        return $brand->fresh();
    }
}
