<?php

namespace App\Modules\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Http\Requests\IndexStockCountRequest;
use App\Modules\Audit\Http\Requests\StoreStockCountRequest;
use App\Modules\Audit\Http\Requests\UpdateStockCountStatusRequest;
use App\Modules\Audit\Http\Resources\StockCountResource;
use App\Modules\Audit\Models\StockCount;
use App\Modules\Audit\Services\StockCountService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockCountController extends Controller
{
    public function __construct(private readonly StockCountService $counts) {}

    public function index(IndexStockCountRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StockCount::class);

        return StockCountResource::collection(
            $this->counts->paginate(TenantContext::fromRequest($request), $request->validated()),
        );
    }

    public function store(StoreStockCountRequest $request): JsonResponse
    {
        $this->authorize('create', StockCount::class);
        $context = TenantContext::fromRequest($request);
        /** @var User $user */
        $user = $request->user();

        return (new StockCountResource($this->counts->create($context, $user, $request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): StockCountResource
    {
        $context = TenantContext::fromRequest($request);
        $count = $this->counts->find($context, $id);
        $this->authorize('view', $count);

        return new StockCountResource($count);
    }

    public function updateStatus(UpdateStockCountStatusRequest $request, int $id): StockCountResource
    {
        $context = TenantContext::fromRequest($request);
        $count = $this->counts->find($context, $id);
        $status = $request->string('status')->toString();

        $this->authorize($status === StockCount::STATUS_APPROVED ? 'approve' : 'review', $count);

        /** @var User $user */
        $user = $request->user();

        return new StockCountResource($this->counts->changeStatus(
            $context,
            $user,
            $count,
            $status,
            $request->string('reason')->toString(),
        ));
    }
}
