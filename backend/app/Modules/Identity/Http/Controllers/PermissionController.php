<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\IndexPermissionsRequest;
use App\Modules\Identity\Http\Resources\PermissionResource;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Services\PermissionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function index(IndexPermissionsRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Permission::class);

        return PermissionResource::collection(
            $this->permissions->paginate($request->validated()),
        );
    }
}
