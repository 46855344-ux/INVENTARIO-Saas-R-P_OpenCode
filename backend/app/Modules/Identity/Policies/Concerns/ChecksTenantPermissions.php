<?php

namespace App\Modules\Identity\Policies\Concerns;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;

trait ChecksTenantPermissions
{
    private function allows(User $user, string $permission): bool
    {
        $context = $this->tenantContext();

        return $context !== null
            && (int) $context->membership->user_id === (int) $user->id
            && $context->hasPermission($permission);
    }

    private function tenantContext(): ?TenantContext
    {
        $context = request()->attributes->get(TenantContext::class);

        return $context instanceof TenantContext ? $context : null;
    }
}
