<?php

namespace App\Modules\Identity\Policies\Concerns;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Database\Eloquent\Model;

trait ChecksTenantPermissions
{
    private function allows(User $user, string $permission): bool
    {
        $context = $this->tenantContext();

        return $context !== null
            && (int) $context->membership->user_id === (int) $user->id
            && $context->hasPermission($permission);
    }

    private function belongsToTenant(Model $model): bool
    {
        $context = $this->tenantContext();

        return $context !== null
            && (int) $model->getAttribute('company_id') === (int) $context->company->id;
    }

    private function tenantContext(): ?TenantContext
    {
        $context = request()->attributes->get(TenantContext::class);

        return $context instanceof TenantContext ? $context : null;
    }
}
