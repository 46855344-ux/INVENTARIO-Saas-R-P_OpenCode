<?php

namespace App\Modules\Purchasing\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Purchasing\Models\Supplier;

class SupplierPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'purchases.view');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $this->allows($user, 'purchases.view') && $this->belongsToTenant($supplier);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'purchases.manage');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->allows($user, 'purchases.manage') && $this->belongsToTenant($supplier);
    }

    public function changeStatus(User $user, Supplier $supplier): bool
    {
        return $this->update($user, $supplier);
    }
}
