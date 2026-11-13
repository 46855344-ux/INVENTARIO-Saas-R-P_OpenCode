<?php

namespace App\Modules\Identity\Policies;

use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\Concerns\ChecksTenantPermissions;

/**
 * Autorización de las operaciones sobre la pertenencia de un usuario.
 */
class UserPolicy
{
    use ChecksTenantPermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'identity.users.view');
    }

    public function view(User $user, CompanyUser $membership): bool
    {
        return $this->allows($user, 'identity.users.view')
            && $this->belongsToTenant($membership);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'identity.users.manage');
    }

    public function update(User $user, CompanyUser $membership): bool
    {
        return $this->allows($user, 'identity.users.manage')
            && $this->belongsToTenant($membership);
    }

    public function changeStatus(User $user, CompanyUser $membership): bool
    {
        return $this->update($user, $membership);
    }

    public function assignRole(User $user, CompanyUser $membership): bool
    {
        return $this->update($user, $membership);
    }

    private function belongsToTenant(CompanyUser $membership): bool
    {
        $context = $this->tenantContext();

        return $context !== null
            && (int) $membership->company_id === (int) $context->company->id;
    }
}
