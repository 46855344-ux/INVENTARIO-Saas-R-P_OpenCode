<?php

namespace App\Modules\Shared\Tenancy;

use App\Modules\Identity\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra toda consulta del modelo por la empresa activa de la petición.
 *
 * Si no hay contexto (comandos, seeders o tareas internas), no agrega el
 * filtro: esos procesos deben acotar explícitamente por `company_id`.
 */
final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = TenantContext::current();

        if ($context === null) {
            return;
        }

        $builder->where(
            $model->getTable().'.company_id',
            $context->company->id,
        );
    }
}
