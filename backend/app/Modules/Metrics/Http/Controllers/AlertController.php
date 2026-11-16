<?php

namespace App\Modules\Metrics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Metrics\Http\Requests\IndexAlertRequest;
use App\Modules\Metrics\Http\Requests\UpdateAlertStatusRequest;
use App\Modules\Metrics\Http\Resources\StockAlertResource;
use App\Modules\Metrics\Models\StockAlert;
use App\Modules\Metrics\Services\InventoryAlertService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertController extends Controller
{
    public function __construct(private readonly InventoryAlertService $alerts) {}

    public function index(IndexAlertRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StockAlert::class);

        $context = TenantContext::fromRequest($request);

        return StockAlertResource::collection(
            $this->alerts->paginateAlerts($context, $request->validated()),
        );
    }

    public function updateStatus(UpdateAlertStatusRequest $request, int $id): StockAlertResource
    {
        $context = TenantContext::fromRequest($request);
        $alert = $this->alerts->findAlert($context, $id);

        $this->authorize('changeStatus', $alert);

        return new StockAlertResource(
            $this->alerts->changeAlertStatus($alert, $request->string('status')->toString()),
        );
    }
}
