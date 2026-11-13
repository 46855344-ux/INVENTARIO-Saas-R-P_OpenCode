<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\IndexRolesRequest;
use App\Modules\Identity\Http\Resources\RoleResource;
use App\Modules\Identity\Services\RoleService;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    public function index(IndexRolesRequest $request): AnonymousResourceCollection
    {
        $context = TenantContext::fromRequest($request);

        return RoleResource::collection(
            $this->roles->paginate($context, $request->validated()),
        );
    }
}
