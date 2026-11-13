<?php

namespace App\Modules\Catalog\Policies;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;

class ProductVariantPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'catalog.view');
    }

    public function view(User $user, ProductVariant $variant): bool
    {
        return $this->allows($user, 'catalog.view') && $this->belongsToTenant($variant);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'catalog.manage');
    }

    public function update(User $user, ProductVariant $variant): bool
    {
        return $this->allows($user, 'catalog.manage') && $this->belongsToTenant($variant);
    }

    public function changeStatus(User $user, ProductVariant $variant): bool
    {
        return $this->update($user, $variant);
    }
}
