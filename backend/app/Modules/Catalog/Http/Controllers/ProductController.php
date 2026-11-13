<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\IndexProductRequest;
use App\Modules\Catalog\Http\Requests\StoreProductRequest;
use App\Modules\Catalog\Http\Requests\UpdateCatalogStatusRequest;
use App\Modules\Catalog\Http\Requests\UpdateProductRequest;
use App\Modules\Catalog\Http\Resources\ProductResource;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        $context = TenantContext::fromRequest($request);

        return ProductResource::collection(
            $this->catalog->paginateProducts($context, $request->validated()),
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $context = TenantContext::fromRequest($request);
        $product = $this->catalog->createProduct($context, $request->validated());

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): ProductResource
    {
        $context = TenantContext::fromRequest($request);
        $product = $this->catalog->findProduct($context, $id);

        $this->authorize('view', $product);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $context = TenantContext::fromRequest($request);
        $product = $this->catalog->findProduct($context, $id);

        $this->authorize('update', $product);

        return new ProductResource(
            $this->catalog->updateProduct($context, $product, $request->validated()),
        );
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, int $id): ProductResource
    {
        $context = TenantContext::fromRequest($request);
        $product = $this->catalog->findProduct($context, $id);

        $this->authorize('changeStatus', $product);

        return new ProductResource(
            $this->catalog->changeProductStatus($product, $request->string('status')->toString()),
        );
    }
}
