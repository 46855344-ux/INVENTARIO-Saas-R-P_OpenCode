<?php

namespace App\Modules\Inventory\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Inventory\Models\Unit;

class UnitPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'settings.view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->allows($user, 'settings.view') && $this->belongsToTenant($unit);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'settings.manage');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->allows($user, 'settings.manage') && $this->belongsToTenant($unit);
    }

    public function changeStatus(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }
}
