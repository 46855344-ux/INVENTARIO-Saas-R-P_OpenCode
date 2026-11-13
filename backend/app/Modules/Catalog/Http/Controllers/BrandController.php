<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\IndexCatalogRequest;
use App\Modules\Catalog\Http\Requests\StoreBrandRequest;
use App\Modules\Catalog\Http\Requests\UpdateBrandRequest;
use App\Modules\Catalog\Http\Requests\UpdateCatalogStatusRequest;
use App\Modules\Catalog\Http\Resources\BrandResource;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(IndexCatalogRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Brand::class);

        $context = TenantContext::fromRequest($request);

        return BrandResource::collection(
            $this->catalog->paginateBrands($context, $request->validated()),
        );
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $this->authorize('create', Brand::class);

        $context = TenantContext::fromRequest($request);
        $brand = $this->catalog->createBrand($context, $request->validated());

        return (new BrandResource($brand))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): BrandResource
    {
        $context = TenantContext::fromRequest($request);
        $brand = $this->catalog->findBrand($context, $id);

        $this->authorize('view', $brand);

        return new BrandResource($brand);
    }

    public function update(UpdateBrandRequest $request, int $id): BrandResource
    {
        $context = TenantContext::fromRequest($request);
        $brand = $this->catalog->findBrand($context, $id);

        $this->authorize('update', $brand);

        return new BrandResource(
            $this->catalog->updateBrand($brand, $request->validated()),
        );
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, int $id): BrandResource
    {
        $context = TenantContext::fromRequest($request);
        $brand = $this->catalog->findBrand($context, $id);

        $this->authorize('changeStatus', $brand);

        return new BrandResource(
            $this->catalog->changeBrandStatus($brand, $request->string('status')->toString()),
        );
    }
}
