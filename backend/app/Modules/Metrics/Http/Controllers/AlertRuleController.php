<?php

namespace App\Modules\Metrics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Metrics\Http\Requests\IndexAlertRuleRequest;
use App\Modules\Metrics\Http\Requests\StoreAlertRuleRequest;
use App\Modules\Metrics\Http\Requests\UpdateAlertRuleRequest;
use App\Modules\Metrics\Http\Resources\AlertRuleResource;
use App\Modules\Metrics\Models\AlertRule;
use App\Modules\Metrics\Services\InventoryAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertRuleController extends Controller
{
    public function __construct(private readonly InventoryAlertService $alerts) {}

    public function index(IndexAlertRuleRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AlertRule::class);

        $context = TenantContext::fromRequest($request);

        return AlertRuleResource::collection(
            $this->alerts->paginateRules($context, $request->validated()),
        );
    }

    public function store(StoreAlertRuleRequest $request): JsonResponse
    {
        $this->authorize('create', AlertRule::class);

        $context = TenantContext::fromRequest($request);
        $rule = $this->alerts->createRule($context, $request->validated());

        return (new AlertRuleResource($rule))->response()->setStatusCode(201);
    }

    public function update(UpdateAlertRuleRequest $request, int $id): AlertRuleResource
    {
        $context = TenantContext::fromRequest($request);
        $rule = $this->alerts->findRule($context, $id);

        $this->authorize('update', $rule);

        return new AlertRuleResource(
            $this->alerts->updateRule($rule, $request->validated()),
        );
    }
}
