<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Http\Requests\IndexLocationRequest;
use App\Modules\Inventory\Http\Requests\StoreLocationRequest;
use App\Modules\Inventory\Http\Requests\UpdateLocationRequest;
use App\Modules\Inventory\Http\Resources\LocationResource;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Services\LocationService;
use App\Modules\Shared\Http\Requests\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function __construct(private readonly LocationService $locations) {}

    public function index(IndexLocationRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $context = TenantContext::fromRequest($request);

        return LocationResource::collection(
            $this->locations->paginate($context, $request->validated()),
        );
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $this->authorize('create', Location::class);

        $context = TenantContext::fromRequest($request);
        $location = $this->locations->create($context, $request->validated());

        return (new LocationResource($location))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): LocationResource
    {
        $context = TenantContext::fromRequest($request);
        $location = $this->locations->find($context, $id);

        $this->authorize('view', $location);

        return new LocationResource($location);
    }

    public function update(UpdateLocationRequest $request, int $id): LocationResource
    {
        $context = TenantContext::fromRequest($request);
        $location = $this->locations->find($context, $id);

        $this->authorize('update', $location);

        return new LocationResource(
            $this->locations->update($location, $request->validated()),
        );
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): LocationResource
    {
        $context = TenantContext::fromRequest($request);
        $location = $this->locations->find($context, $id);

        $this->authorize('changeStatus', $location);

        return new LocationResource(
            $this->locations->changeStatus($location, $request->string('status')->toString()),
        );
    }
}
