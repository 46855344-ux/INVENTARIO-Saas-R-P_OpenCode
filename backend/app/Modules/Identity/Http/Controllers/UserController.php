<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\AssignRoleRequest;
use App\Modules\Identity\Http\Requests\IndexUsersRequest;
use App\Modules\Identity\Http\Requests\StoreUserRequest;
use App\Modules\Identity\Http\Requests\UpdateUserRequest;
use App\Modules\Identity\Http\Requests\UpdateUserStatusRequest;
use App\Modules\Identity\Http\Resources\CompanyUserResource;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Identity\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index(IndexUsersRequest $request): AnonymousResourceCollection
    {
        $context = TenantContext::fromRequest($request);

        return CompanyUserResource::collection(
            $this->users->paginate($context, $request->validated()),
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $context = TenantContext::fromRequest($request);
        $membership = $this->users->create($context, $request->validated());

        return (new CompanyUserResource($membership))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): CompanyUserResource
    {
        $context = TenantContext::fromRequest($request);

        return new CompanyUserResource($this->users->find($context, $id));
    }

    public function update(UpdateUserRequest $request, int $id): CompanyUserResource
    {
        $context = TenantContext::fromRequest($request);

        return new CompanyUserResource($this->users->update($context, $id, $request->validated()));
    }

    public function updateStatus(UpdateUserStatusRequest $request, int $id): CompanyUserResource
    {
        $context = TenantContext::fromRequest($request);

        return new CompanyUserResource(
            $this->users->changeStatus($context, $id, $request->string('status')->toString()),
        );
    }

    public function assignRole(AssignRoleRequest $request, int $id): CompanyUserResource
    {
        $context = TenantContext::fromRequest($request);

        return new CompanyUserResource(
            $this->users->assignRole($context, $id, (int) $request->input('role_id')),
        );
    }
}
