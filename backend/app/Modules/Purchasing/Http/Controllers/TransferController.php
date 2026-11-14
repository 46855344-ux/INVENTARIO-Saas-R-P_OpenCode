<?php

namespace App\Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Purchasing\Http\Requests\IndexTransferRequest;
use App\Modules\Purchasing\Http\Requests\StoreTransferRequest;
use App\Modules\Purchasing\Http\Resources\TransferResource;
use App\Modules\Purchasing\Models\Transfer;
use App\Modules\Purchasing\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransferController extends Controller
{
    public function __construct(private readonly TransferService $transfers) {}

    public function index(IndexTransferRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Transfer::class);

        $context = TenantContext::fromRequest($request);

        return TransferResource::collection(
            $this->transfers->paginate($context, $request->validated()),
        );
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        $this->authorize('create', Transfer::class);

        $context = TenantContext::fromRequest($request);

        /** @var User $user */
        $user = $request->user();

        $transfer = $this->transfers->execute(
            $context,
            $user,
            $request->validated(),
            $request->idempotencyKey(),
        );

        $status = $transfer->wasRecentlyCreated ? 201 : 200;

        return (new TransferResource($transfer))->response()->setStatusCode($status);
    }

    public function show(Request $request, int $id): TransferResource
    {
        $context = TenantContext::fromRequest($request);
        $transfer = $this->transfers->find($context, $id);

        $this->authorize('view', $transfer);

        return new TransferResource($transfer);
    }
}
