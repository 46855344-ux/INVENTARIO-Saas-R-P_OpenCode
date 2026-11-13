<?php

namespace App\Modules\Catalog\Policies;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;

class BrandPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'catalog.view');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $this->allows($user, 'catalog.view') && $this->belongsToTenant($brand);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'catalog.manage');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $this->allows($user, 'catalog.manage') && $this->belongsToTenant($brand);
    }

    public function changeStatus(User $user, Brand $brand): bool
    {
        return $this->update($user, $brand);
    }
}
