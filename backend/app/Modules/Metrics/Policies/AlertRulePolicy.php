<?php

namespace App\Modules\Metrics\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Metrics\Models\AlertRule;

class AlertRulePolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'alerts.view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'alerts.manage');
    }

    public function update(User $user, AlertRule $rule): bool
    {
        return $this->allows($user, 'alerts.manage') && $this->belongsToTenant($rule);
    }
}
