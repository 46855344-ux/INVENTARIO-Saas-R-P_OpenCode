<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\IndexCatalogRequest;
use App\Modules\Catalog\Http\Requests\StoreCategoryRequest;
use App\Modules\Catalog\Http\Requests\UpdateCatalogStatusRequest;
use App\Modules\Catalog\Http\Requests\UpdateCategoryRequest;
use App\Modules\Catalog\Http\Resources\CategoryResource;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(IndexCatalogRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Category::class);

        $context = TenantContext::fromRequest($request);

        return CategoryResource::collection(
            $this->catalog->paginateCategories($context, $request->validated()),
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $context = TenantContext::fromRequest($request);
        $category = $this->catalog->createCategory($context, $request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): CategoryResource
    {
        $context = TenantContext::fromRequest($request);
        $category = $this->catalog->findCategory($context, $id);

        $this->authorize('view', $category);

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, int $id): CategoryResource
    {
        $context = TenantContext::fromRequest($request);
        $category = $this->catalog->findCategory($context, $id);

        $this->authorize('update', $category);

        return new CategoryResource(
            $this->catalog->updateCategory($category, $request->validated()),
        );
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, int $id): CategoryResource
    {
        $context = TenantContext::fromRequest($request);
        $category = $this->catalog->findCategory($context, $id);

        $this->authorize('changeStatus', $category);

        return new CategoryResource(
            $this->catalog->changeCategoryStatus($category, $request->string('status')->toString()),
        );
    }
}
