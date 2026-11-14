<?php

namespace App\Modules\Purchasing\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;
use App\Modules\Purchasing\Models\Transfer;

class TransferPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'transfers.view');
    }

    public function view(User $user, Transfer $transfer): bool
    {
        return $this->allows($user, 'transfers.view') && $this->belongsToTenant($transfer);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'transfers.manage');
    }
}
