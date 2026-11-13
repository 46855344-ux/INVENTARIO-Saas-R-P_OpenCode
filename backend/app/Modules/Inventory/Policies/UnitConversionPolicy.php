<?php

namespace App\Modules\Inventory\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Inventory\Models\UnitConversion;

class UnitConversionPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'settings.view');
    }

    public function view(User $user, UnitConversion $conversion): bool
    {
        return $this->allows($user, 'settings.view') && $this->belongsToTenant($conversion);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'settings.manage');
    }

    public function update(User $user, UnitConversion $conversion): bool
    {
        return $this->allows($user, 'settings.manage') && $this->belongsToTenant($conversion);
    }

    public function changeStatus(User $user, UnitConversion $conversion): bool
    {
        return $this->update($user, $conversion);
    }
}
