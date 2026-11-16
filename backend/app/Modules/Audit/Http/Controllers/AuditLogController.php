<?php

namespace App\Modules\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Http\Requests\IndexAuditRequest;
use App\Modules\Audit\Http\Resources\AuditLogResource;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function index(IndexAuditRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AuditLog::class);

        return AuditLogResource::collection(
            $this->audit->paginate(TenantContext::fromRequest($request), $request->validated()),
        );
    }
}
