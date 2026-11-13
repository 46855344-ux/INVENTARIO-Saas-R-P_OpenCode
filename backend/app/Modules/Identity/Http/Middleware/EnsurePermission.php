<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Modules\Identity\Exceptions\ForbiddenException;
use App\Modules\Identity\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige uno o más permisos del rol activo en la empresa de la petición.
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $context = TenantContext::fromRequest($request);

        foreach ($permissions as $permission) {
            if (! $context->hasPermission($permission)) {
                throw new ForbiddenException;
            }
        }

        return $next($request);
    }
}
