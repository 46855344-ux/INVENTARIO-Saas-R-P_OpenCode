<?php

namespace App\Modules\Metrics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Http\Requests\IndexStockRequest;
use App\Modules\Inventory\Http\Resources\StockBalanceResource;
use App\Modules\Metrics\Http\Requests\OverviewMetricsRequest;
use App\Modules\Metrics\Services\InventoryMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MetricsController extends Controller
{
    public function __construct(private readonly InventoryMetricsService $metrics) {}

    public function overview(OverviewMetricsRequest $request): JsonResponse
    {
        $context = TenantContext::fromRequest($request);

        return response()->json([
            'data' => $this->metrics->overview($context, $request->validated()),
        ]);
    }

    public function stockDetail(IndexStockRequest $request): AnonymousResourceCollection
    {
        $context = TenantContext::fromRequest($request);

        return StockBalanceResource::collection(
            $this->metrics->stockDetail($context, $request->validated()),
        );
    }
}
