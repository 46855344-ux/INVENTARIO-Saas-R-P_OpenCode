<?php

namespace App\Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Purchasing\Http\Requests\IndexSupplierRequest;
use App\Modules\Purchasing\Http\Requests\StoreSupplierRequest;
use App\Modules\Purchasing\Http\Requests\UpdateSupplierRequest;
use App\Modules\Purchasing\Http\Resources\SupplierResource;
use App\Modules\Purchasing\Models\Supplier;
use App\Modules\Purchasing\Services\SupplierService;
use App\Modules\Shared\Http\Requests\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers) {}

    public function index(IndexSupplierRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Supplier::class);

        $context = TenantContext::fromRequest($request);

        return SupplierResource::collection(
            $this->suppliers->paginate($context, $request->validated()),
        );
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $this->authorize('create', Supplier::class);

        $context = TenantContext::fromRequest($request);
        $supplier = $this->suppliers->create($context, $request->validated());

        return (new SupplierResource($supplier))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): SupplierResource
    {
        $context = TenantContext::fromRequest($request);
        $supplier = $this->suppliers->find($context, $id);

        $this->authorize('view', $supplier);

        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, int $id): SupplierResource
    {
        $context = TenantContext::fromRequest($request);
        $supplier = $this->suppliers->find($context, $id);

        $this->authorize('update', $supplier);

        return new SupplierResource(
            $this->suppliers->update($supplier, $request->validated()),
        );
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): SupplierResource
    {
        $context = TenantContext::fromRequest($request);
        $supplier = $this->suppliers->find($context, $id);

        $this->authorize('changeStatus', $supplier);

        return new SupplierResource(
            $this->suppliers->changeStatus($supplier, $request->string('status')->toString()),
        );
    }
}
