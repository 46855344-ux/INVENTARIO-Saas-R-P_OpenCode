<?php

namespace App\Modules\Audit\Policies;

use App\Modules\Audit\Models\StockCount;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;

class StockCountPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'counts.view');
    }

    public function view(User $user, StockCount $count): bool
    {
        return $this->allows($user, 'counts.view') && $this->belongsToTenant($count);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'counts.manage');
    }

    public function review(User $user, StockCount $count): bool
    {
        return $this->allows($user, 'counts.manage') && $this->belongsToTenant($count);
    }

    public function approve(User $user, StockCount $count): bool
    {
        return $this->allows($user, 'counts.approve') && $this->belongsToTenant($count);
    }
}
