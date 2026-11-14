<?php

namespace App\Modules\Inventory\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Inventory\Models\StockBalance;

class StockBalancePolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'inventory.view');
    }

    public function view(User $user, StockBalance $balance): bool
    {
        return $this->allows($user, 'inventory.view') && $this->belongsToTenant($balance);
    }
}
