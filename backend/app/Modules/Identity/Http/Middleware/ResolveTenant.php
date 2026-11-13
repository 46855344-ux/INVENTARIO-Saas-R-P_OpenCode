<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Modules\Identity\Exceptions\ForbiddenTenantException;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve la empresa activa desde la cabecera `X-Company-Id` y valida que el
 * usuario tenga una pertenencia activa (ver ADR-0002).
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            throw new AuthenticationException;
        }

        $companyId = $request->header('X-Company-Id');

        if (! is_string($companyId) || ! ctype_digit($companyId)) {
            throw ForbiddenTenantException::missingCompany();
        }

        $membership = CompanyUser::query()
            ->where('company_id', (int) $companyId)
            ->where('user_id', $user->id)
            ->where('status', CompanyUser::STATUS_ACTIVE)
            ->whereHas('company', fn (Builder $query) => $query->where('status', Company::STATUS_ACTIVE))
            ->with(['company', 'role.permissions'])
            ->first();

        if ($membership === null) {
            throw ForbiddenTenantException::notAllowed();
        }

        $request->attributes->set(TenantContext::class, new TenantContext(
            $membership->company,
            $membership,
            $membership->role,
            $membership->role?->permissionCodes() ?? [],
        ));

        return $next($request);
    }
}
