<?php

namespace App\Modules\Metrics\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Metrics\Models\StockAlert;

class StockAlertPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'alerts.view');
    }

    public function changeStatus(User $user, StockAlert $alert): bool
    {
        return $this->allows($user, 'alerts.manage') && $this->belongsToTenant($alert);
    }
}
