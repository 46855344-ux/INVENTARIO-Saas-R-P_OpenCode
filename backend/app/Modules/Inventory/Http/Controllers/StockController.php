<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Http\Requests\IndexStockRequest;
use App\Modules\Inventory\Http\Resources\StockBalanceResource;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Services\InventoryMovementService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function __construct(private readonly InventoryMovementService $inventory) {}

    public function index(IndexStockRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StockBalance::class);

        $context = TenantContext::fromRequest($request);

        return StockBalanceResource::collection(
            $this->inventory->paginateStock($context, $request->validated()),
        );
    }
}
