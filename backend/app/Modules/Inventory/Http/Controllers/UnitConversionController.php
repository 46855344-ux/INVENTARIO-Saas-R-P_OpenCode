<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Http\Requests\IndexConversionRequest;
use App\Modules\Inventory\Http\Requests\SimulateConversionRequest;
use App\Modules\Inventory\Http\Requests\StoreConversionRequest;
use App\Modules\Inventory\Http\Requests\UpdateConversionRequest;
use App\Modules\Inventory\Http\Resources\UnitConversionResource;
use App\Modules\Inventory\Models\UnitConversion;
use App\Modules\Inventory\Services\UnitConversionService;
use App\Modules\Inventory\Services\UnitService;
use App\Modules\Shared\Http\Requests\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitConversionController extends Controller
{
    public function __construct(
        private readonly UnitConversionService $conversions,
        private readonly UnitService $units,
    ) {}

    public function index(IndexConversionRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', UnitConversion::class);

        $context = TenantContext::fromRequest($request);

        return UnitConversionResource::collection(
            $this->conversions->paginate($context, $request->validated()),
        );
    }

    public function store(StoreConversionRequest $request): JsonResponse
    {
        $this->authorize('create', UnitConversion::class);

        $context = TenantContext::fromRequest($request);
        $conversion = $this->conversions->create($context, $request->validated());

        return (new UnitConversionResource($conversion))->response()->setStatusCode(201);
    }

    public function update(UpdateConversionRequest $request, int $id): UnitConversionResource
    {
        $context = TenantContext::fromRequest($request);
        $conversion = $this->conversions->find($context, $id);

        $this->authorize('update', $conversion);

        return new UnitConversionResource(
            $this->conversions->update($conversion, $request->validated()),
        );
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): UnitConversionResource
    {
        $context = TenantContext::fromRequest($request);
        $conversion = $this->conversions->find($context, $id);

        $this->authorize('changeStatus', $conversion);

        return new UnitConversionResource(
            $this->conversions->changeStatus($conversion, $request->string('status')->toString()),
        );
    }

    public function simulate(SimulateConversionRequest $request): JsonResponse
    {
        $this->authorize('viewAny', UnitConversion::class);

        $context = TenantContext::fromRequest($request);
        $from = $this->units->find($context, (int) $request->input('from_unit_id'));
        $to = $this->units->find($context, (int) $request->input('to_unit_id'));

        $result = $this->conversions->convert(
            $from,
            $to,
            $request->string('quantity')->toString(),
        );

        return response()->json(['data' => $result]);
    }
}
