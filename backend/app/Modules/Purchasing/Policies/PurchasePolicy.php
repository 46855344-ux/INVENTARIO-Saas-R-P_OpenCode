<?php

namespace App\Modules\Purchasing\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Purchasing\Models\Purchase;

class PurchasePolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'purchases.view');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $this->allows($user, 'purchases.view') && $this->belongsToTenant($purchase);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'purchases.manage');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $this->allows($user, 'purchases.manage') && $this->belongsToTenant($purchase);
    }

    public function receive(User $user, Purchase $purchase): bool
    {
        return $this->allows($user, 'purchases.receive') && $this->belongsToTenant($purchase);
    }
}
