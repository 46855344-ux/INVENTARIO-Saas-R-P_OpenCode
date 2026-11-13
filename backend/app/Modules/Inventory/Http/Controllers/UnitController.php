<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Http\Requests\IndexUnitRequest;
use App\Modules\Inventory\Http\Requests\StoreUnitRequest;
use App\Modules\Inventory\Http\Requests\UpdateUnitRequest;
use App\Modules\Inventory\Http\Resources\UnitResource;
use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Services\UnitService;
use App\Modules\Shared\Http\Requests\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $units) {}

    public function index(IndexUnitRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Unit::class);

        $context = TenantContext::fromRequest($request);

        return UnitResource::collection(
            $this->units->paginate($context, $request->validated()),
        );
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $this->authorize('create', Unit::class);

        $context = TenantContext::fromRequest($request);
        $unit = $this->units->create($context, $request->validated());

        return (new UnitResource($unit))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): UnitResource
    {
        $context = TenantContext::fromRequest($request);
        $unit = $this->units->find($context, $id);

        $this->authorize('view', $unit);

        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, int $id): UnitResource
    {
        $context = TenantContext::fromRequest($request);
        $unit = $this->units->find($context, $id);

        $this->authorize('update', $unit);

        return new UnitResource($this->units->update($unit, $request->validated()));
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): UnitResource
    {
        $context = TenantContext::fromRequest($request);
        $unit = $this->units->find($context, $id);

        $this->authorize('changeStatus', $unit);

        return new UnitResource(
            $this->units->changeStatus($unit, $request->string('status')->toString()),
        );
    }
}
