<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Catálogo global de permisos (`modulo.accion`).
 */
class PermissionService
{
    /**
     * @param  array{per_page?: int|null}  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Permission::query()
            ->orderBy('code')
            ->paginate($filters['per_page'] ?? 50)
            ->withQueryString();
    }
}
