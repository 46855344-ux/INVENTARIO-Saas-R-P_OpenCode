<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Http\Requests\IndexMovementRequest;
use App\Modules\Inventory\Http\Requests\ReverseMovementRequest;
use App\Modules\Inventory\Http\Requests\StoreAdjustmentRequest;
use App\Modules\Inventory\Http\Requests\StoreMovementRequest;
use App\Modules\Inventory\Http\Requests\StoreReturnRequest;
use App\Modules\Inventory\Http\Resources\StockMovementResource;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Services\InventoryMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockMovementController extends Controller
{
    public function __construct(private readonly InventoryMovementService $inventory) {}

    public function index(IndexMovementRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StockMovement::class);

        $context = TenantContext::fromRequest($request);

        return StockMovementResource::collection(
            $this->inventory->paginateMovements($context, $request->validated()),
        );
    }

    public function show(Request $request, int $id): StockMovementResource
    {
        $context = TenantContext::fromRequest($request);
        $movement = $this->inventory->findMovement($context, $id);

        $this->authorize('view', $movement);

        return new StockMovementResource($movement);
    }

    public function storeEntry(StoreMovementRequest $request): JsonResponse
    {
        return $this->store($request, StockMovement::TYPE_ENTRY, 'createEntry');
    }

    public function storeExit(StoreMovementRequest $request): JsonResponse
    {
        return $this->store($request, StockMovement::TYPE_EXIT, 'createExit');
    }

    public function storeReturn(StoreReturnRequest $request): JsonResponse
    {
        return $this->store($request, StockMovement::TYPE_RETURN, 'createReturn');
    }

    public function storeAdjustment(StoreAdjustmentRequest $request): JsonResponse
    {
        return $this->store($request, StockMovement::TYPE_ADJUSTMENT, 'createAdjustment');
    }

    public function reverse(ReverseMovementRequest $request, int $id): JsonResponse
    {
        $context = TenantContext::fromRequest($request);
        $movement = $this->inventory->findMovement($context, $id);

        $this->authorize('reverse', $movement);

        /** @var User $user */
        $user = $request->user();

        $reversal = $this->inventory->reverse(
            $context,
            $user,
            $movement,
            $request->string('reason')->toString(),
        );

        return (new StockMovementResource($reversal))->response()->setStatusCode(201);
    }

    private function store(StoreMovementRequest $request, string $type, string $ability): JsonResponse
    {
        $this->authorize($ability, StockMovement::class);

        $context = TenantContext::fromRequest($request);

        /** @var User $user */
        $user = $request->user();

        $movement = $this->inventory->record($context, $user, $type, $request->validated());

        $status = $movement->wasRecentlyCreated ? 201 : 200;

        return (new StockMovementResource($movement))->response()->setStatusCode($status);
    }
}
