<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Catálogo de roles disponibles para la empresa activa.
 */
class RoleService
{
    /**
     * @param  array{per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return Role::query()
            ->where(function ($query) use ($context): void {
                $query->whereNull('company_id')
                    ->orWhere('company_id', $context->company->id);
            })
            ->with('permissions')
            ->orderBy('company_id')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }
}
