<?php

namespace App\Modules\Shared\Tenancy;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de dominio acotado a una empresa (ver SDD-04 §2 y ADR-0002).
 *
 * - Aplica `TenantScope` en toda consulta.
 * - Asigna `company_id` desde el contexto al crear, ignorando el payload.
 * - Impide mover un registro a otra empresa al actualizar.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $context = TenantContext::current();

            if ($context !== null) {
                $model->setAttribute('company_id', $context->company->id);
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('company_id')) {
                $model->setAttribute('company_id', $model->getOriginal('company_id'));
            }
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Comprueba que un registro relacionado pertenezca a la misma empresa.
     */
    public function belongsToSameCompany(Model $related): bool
    {
        return (int) $this->getAttribute('company_id') === (int) $related->getAttribute('company_id');
    }
}
