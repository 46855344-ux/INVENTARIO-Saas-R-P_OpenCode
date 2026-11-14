<?php

namespace App\Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Purchasing\Http\Requests\IndexPurchaseRequest;
use App\Modules\Purchasing\Http\Requests\ReceivePurchaseRequest;
use App\Modules\Purchasing\Http\Requests\StorePurchaseRequest;
use App\Modules\Purchasing\Http\Requests\UpdatePurchaseRequest;
use App\Modules\Purchasing\Http\Resources\PurchaseResource;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchases) {}

    public function index(IndexPurchaseRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Purchase::class);

        $context = TenantContext::fromRequest($request);

        return PurchaseResource::collection(
            $this->purchases->paginate($context, $request->validated()),
        );
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $this->authorize('create', Purchase::class);

        $context = TenantContext::fromRequest($request);

        /** @var User $user */
        $user = $request->user();

        $purchase = $this->purchases->create($context, $user, $request->validated());

        return (new PurchaseResource($purchase))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): PurchaseResource
    {
        $context = TenantContext::fromRequest($request);
        $purchase = $this->purchases->find($context, $id);

        $this->authorize('view', $purchase);

        return new PurchaseResource($purchase);
    }

    public function update(UpdatePurchaseRequest $request, int $id): PurchaseResource
    {
        $context = TenantContext::fromRequest($request);
        $purchase = $this->purchases->find($context, $id);

        $this->authorize('update', $purchase);

        return new PurchaseResource(
            $this->purchases->update($context, $purchase, $request->validated()),
        );
    }

    public function receive(ReceivePurchaseRequest $request, int $id): JsonResponse
    {
        $context = TenantContext::fromRequest($request);
        $purchase = $this->purchases->find($context, $id);

        $this->authorize('receive', $purchase);

        /** @var User $user */
        $user = $request->user();

        $received = $this->purchases->receive(
            $context,
            $user,
            $purchase,
            $request->idempotencyKey(),
        );

        return (new PurchaseResource($received))->response()->setStatusCode(200);
    }
}
