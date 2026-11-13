<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Reglas del catálogo: categorías, marcas y productos acotados a la empresa activa.
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

    /**
     * @param  array{search?: string|null, status?: string|null, category_id?: int|null, brand_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginateProducts(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Product::query()
            ->with(['category', 'brand'])
            ->where('company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['category_id']), fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['brand_id']), fn ($query) => $query->where('brand_id', $filters['brand_id']))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, description?: string|null, category_id?: int|null, brand_id?: int|null}  $data
     */
    public function createProduct(TenantContext $context, array $data): Product
    {
        $this->assertProductReferences($context, $data);

        $product = Product::query()->create([
            'company_id' => $context->company->id,
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => Product::STATUS_ACTIVE,
        ]);

        return $product->load(['category', 'brand']);
    }

    public function findProduct(TenantContext $context, int $productId): Product
    {
        $product = Product::query()
            ->with(['category', 'brand'])
            ->where('company_id', $context->company->id)
            ->whereKey($productId)
            ->first();

        if ($product === null) {
            throw (new ModelNotFoundException)->setModel(Product::class, [$productId]);
        }

        return $product;
    }

    /**
     * @param  array{name?: string, description?: string|null, category_id?: int|null, brand_id?: int|null}  $data
     */
    public function updateProduct(TenantContext $context, Product $product, array $data): Product
    {
        $this->assertProductReferences($context, $data);

        $attributes = Arr::only($data, ['name', 'description', 'category_id', 'brand_id']);

        if ($attributes !== []) {
            $product->update($attributes);
        }

        return $product->fresh(['category', 'brand']);
    }

    public function changeProductStatus(Product $product, string $status): Product
    {
        $product->update(['status' => $status]);

        return $product->fresh(['category', 'brand']);
    }

    /**
     * @param  array{search?: string|null, status?: string|null, per_page?: int|null}  $filters
     */
    public function paginateVariants(TenantContext $context, Product $product, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return ProductVariant::query()
            ->where('company_id', $context->company->id)
            ->where('product_id', $product->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, sku: string, barcode?: string|null, attributes?: array<string, mixed>|null, cost?: string|float|int|null, price?: string|float|int|null}  $data
     */
    public function createVariant(TenantContext $context, Product $product, array $data): ProductVariant
    {
        return ProductVariant::query()->create([
            'company_id' => $context->company->id,
            'product_id' => $product->id,
            'name' => $data['name'],
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'attributes' => $data['attributes'] ?? null,
            'cost' => $data['cost'] ?? '0',
            'price' => $data['price'] ?? '0',
            'status' => ProductVariant::STATUS_ACTIVE,
        ]);
    }

    public function findVariant(TenantContext $context, int $variantId): ProductVariant
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

    /**
     * @param  array{name?: string, sku?: string, barcode?: string|null, attributes?: array<string, mixed>|null, cost?: string|float|int|null, price?: string|float|int|null}  $data
     */
    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $attributes = Arr::only($data, ['name', 'sku', 'barcode', 'attributes', 'cost', 'price']);

        if ($attributes !== []) {
            $variant->update($attributes);
        }

        return $variant->fresh();
    }

    public function changeVariantStatus(ProductVariant $variant, string $status): ProductVariant
    {
        $variant->update(['status' => $status]);

        return $variant->fresh();
    }

    /**
     * @param  array{category_id?: int|null, brand_id?: int|null}  $data
     */
    private function assertProductReferences(TenantContext $context, array $data): void
    {
        if (array_key_exists('category_id', $data) && $data['category_id'] !== null) {
            $exists = Category::query()
                ->where('company_id', $context->company->id)
                ->whereKey($data['category_id'])
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'category_id' => ['La categoría no pertenece a la empresa activa.'],
                ]);
            }
        }

        if (array_key_exists('brand_id', $data) && $data['brand_id'] !== null) {
            $exists = Brand::query()
                ->where('company_id', $context->company->id)
                ->whereKey($data['brand_id'])
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'brand_id' => ['La marca no pertenece a la empresa activa.'],
                ]);
            }
        }
    }
}
