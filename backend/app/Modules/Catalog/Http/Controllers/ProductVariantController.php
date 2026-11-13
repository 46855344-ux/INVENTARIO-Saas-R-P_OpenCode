<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\IndexVariantRequest;
use App\Modules\Catalog\Http\Requests\StoreVariantRequest;
use App\Modules\Catalog\Http\Requests\UpdateCatalogStatusRequest;
use App\Modules\Catalog\Http\Requests\UpdateVariantRequest;
use App\Modules\Catalog\Http\Resources\ProductVariantResource;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductVariantController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(IndexVariantRequest $request, int $product): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ProductVariant::class);

        $context = TenantContext::fromRequest($request);
        $productModel = $this->catalog->findProduct($context, $product);

        return ProductVariantResource::collection(
            $this->catalog->paginateVariants($context, $productModel, $request->validated()),
        );
    }

    public function store(StoreVariantRequest $request, int $product): JsonResponse
    {
        $this->authorize('create', ProductVariant::class);

        $context = TenantContext::fromRequest($request);
        $productModel = $this->catalog->findProduct($context, $product);
        $variant = $this->catalog->createVariant($context, $productModel, $request->validated());

        return (new ProductVariantResource($variant))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): ProductVariantResource
    {
        $context = TenantContext::fromRequest($request);
        $variant = $this->catalog->findVariant($context, $id);

        $this->authorize('view', $variant);

        return new ProductVariantResource($variant);
    }

    public function update(UpdateVariantRequest $request, int $id): ProductVariantResource
    {
        $context = TenantContext::fromRequest($request);
        $variant = $this->catalog->findVariant($context, $id);

        $this->authorize('update', $variant);

        return new ProductVariantResource(
            $this->catalog->updateVariant($variant, $request->validated()),
        );
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, int $id): ProductVariantResource
    {
        $context = TenantContext::fromRequest($request);
        $variant = $this->catalog->findVariant($context, $id);

        $this->authorize('changeStatus', $variant);

        return new ProductVariantResource(
            $this->catalog->changeVariantStatus($variant, $request->string('status')->toString()),
        );
    }
}
