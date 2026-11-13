<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Exceptions\ForbiddenException;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reglas de administración de usuarios dentro de la empresa activa.
 */
class UserService
{
    /**
     * @param  array{search?: string|null, status?: string|null, role_id?: int|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return CompanyUser::query()
            ->where('company_user.company_id', $context->company->id)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where(function ($nested) use ($search): void {
                        $nested->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('company_user.status', $filters['status']))
            ->when(isset($filters['role_id']), fn ($query) => $query->where('company_user.role_id', $filters['role_id']))
            ->with(['user', 'role'])
            ->join('users', 'users.id', '=', 'company_user.user_id')
            ->orderBy('users.name')
            ->select('company_user.*')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    public function find(TenantContext $context, int $userId): CompanyUser
    {
        $membership = CompanyUser::query()
            ->where('company_id', $context->company->id)
            ->where('user_id', $userId)
            ->with(['user', 'role'])
            ->first();

        if ($membership === null) {
            throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
        }

        return $membership;
    }

    /**
     * @param  array{name: string, email: string, password: string, role_id: int, status?: string}  $data
     */
    public function create(TenantContext $context, array $data): CompanyUser
    {
        $role = Role::query()->with('permissions')->findOrFail($data['role_id']);
        $this->assertRoleAssignable($context, $role);

        return DB::transaction(function () use ($context, $data, $role): CompanyUser {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => User::STATUS_ACTIVE,
            ]);

            $membership = CompanyUser::query()->create([
                'company_id' => $context->company->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'status' => $data['status'] ?? CompanyUser::STATUS_ACTIVE,
            ]);

            return $membership->load(['user', 'role']);
        });
    }

    /**
     * @param  array{name?: string, email?: string, password?: string|null}  $data
     */
    public function update(TenantContext $context, int $userId, array $data): CompanyUser
    {
        $membership = $this->find($context, $userId);

        $attributes = Arr::only($data, ['name', 'email']);

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        if ($attributes !== []) {
            $membership->user->update($attributes);
        }

        return $membership->fresh(['user', 'role']);
    }

    public function changeStatus(TenantContext $context, int $userId, string $status): CompanyUser
    {
        $membership = $this->find($context, $userId);
        $membership->update(['status' => $status]);

        return $membership->fresh(['user', 'role']);
    }

    public function assignRole(TenantContext $context, int $userId, int $roleId): CompanyUser
    {
        $membership = $this->find($context, $userId);
        $role = Role::query()->with('permissions')->findOrFail($roleId);
        $this->assertRoleAssignable($context, $role);

        $membership->update(['role_id' => $role->id]);

        return $membership->fresh(['user', 'role']);
    }

    private function assertRoleAssignable(TenantContext $context, Role $role): void
    {
        if (! $role->isAvailableTo($context->company->id)) {
            throw ValidationException::withMessages([
                'role_id' => ['El rol seleccionado no pertenece a la empresa activa.'],
            ]);
        }

        if (array_diff($role->permissionCodes(), $context->permissions) !== []) {
            throw new ForbiddenException('No puede asignar un rol con permisos que no posee.');
        }
    }
}
