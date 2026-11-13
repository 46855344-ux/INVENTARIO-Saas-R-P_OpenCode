<?php

namespace App\Modules\Catalog\Policies;

use App\Modules\Catalog\Models\Product;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;

class ProductPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'catalog.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->allows($user, 'catalog.view') && $this->belongsToTenant($product);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'catalog.manage');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->allows($user, 'catalog.manage') && $this->belongsToTenant($product);
    }

    public function changeStatus(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
