<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\Role;
use Illuminate\Http\Request;
use LogicException;

/**
 * Empresa activa de la petición, con la pertenencia y los permisos del usuario.
 * La resuelve el middleware `tenant` a partir de la cabecera `X-Company-Id`
 * (ver ADR-0002).
 */
final readonly class TenantContext
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public Company $company,
        public CompanyUser $membership,
        public ?Role $role,
        public array $permissions,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $context = $request->attributes->get(self::class);

        if (! $context instanceof self) {
            throw new LogicException('El contexto de empresa no fue resuelto.');
        }

        return $context;
    }

    public function hasPermission(string $code): bool
    {
        return in_array($code, $this->permissions, true);
    }
}
