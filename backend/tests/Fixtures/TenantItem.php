<?php

namespace Tests\Fixtures;

use App\Modules\Shared\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de prueba para verificar el aislamiento por empresa sin introducir
 * entidades de dominio antes del Sprint 2.
 */
#[Fillable(['company_id', 'name'])]
class TenantItem extends Model
{
    use BelongsToCompany;

    protected $table = 'tenant_items';
}
