<?php

namespace App\Modules\Inventory\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Inventory\Models\Location;

class LocationPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'settings.view');
    }

    public function view(User $user, Location $location): bool
    {
        return $this->allows($user, 'settings.view') && $this->belongsToTenant($location);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'settings.manage');
    }

    public function update(User $user, Location $location): bool
    {
        return $this->allows($user, 'settings.manage') && $this->belongsToTenant($location);
    }

    public function changeStatus(User $user, Location $location): bool
    {
        return $this->update($user, $location);
    }
}
