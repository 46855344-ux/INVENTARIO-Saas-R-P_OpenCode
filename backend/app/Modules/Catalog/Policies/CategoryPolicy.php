<?php

namespace App\Modules\Catalog\Policies;

use App\Modules\Catalog\Models\Category;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;

class CategoryPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'catalog.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->allows($user, 'catalog.view') && $this->belongsToTenant($category);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'catalog.manage');
    }

    public function update(User $user, Category $category): bool
    {
        return $this->allows($user, 'catalog.manage') && $this->belongsToTenant($category);
    }

    public function changeStatus(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
