<?php

namespace App\Modules\Inventory\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Inventory\Models\StockMovement;

class StockMovementPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'inventory.view');
    }

    public function view(User $user, StockMovement $movement): bool
    {
        return $this->allows($user, 'inventory.view') && $this->belongsToTenant($movement);
    }

    public function createEntry(User $user): bool
    {
        return $this->allows($user, 'inventory.move');
    }

    public function createExit(User $user): bool
    {
        return $this->allows($user, 'inventory.move');
    }

    public function createReturn(User $user): bool
    {
        return $this->allows($user, 'inventory.move');
    }

    public function createAdjustment(User $user): bool
    {
        return $this->allows($user, 'inventory.adjust');
    }

    public function reverse(User $user, StockMovement $movement): bool
    {
        return $this->allows($user, 'inventory.approve') && $this->belongsToTenant($movement);
    }
}
