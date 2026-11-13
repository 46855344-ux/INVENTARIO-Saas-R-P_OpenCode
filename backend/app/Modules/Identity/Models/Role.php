<?php

namespace App\Modules\Identity\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rol de acceso. Los roles base tienen `company_id = NULL`; los roles
 * personalizados pertenecen a una empresa (ver ADR-0003).
 */
#[Fillable(['company_id', 'code', 'name', 'description'])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    public const CODE_OWNER = 'owner';

    public const CODE_ADMIN = 'admin';

    public const CODE_OPERATOR = 'operator';

    public const CODE_VIEWER = 'viewer';

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    /**
     * @return HasMany<CompanyUser, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function isGlobal(): bool
    {
        return $this->company_id === null;
    }

    public function isAvailableTo(int $companyId): bool
    {
        return $this->isGlobal() || (int) $this->company_id === $companyId;
    }

    /**
     * @return list<string>
     */
    public function permissionCodes(): array
    {
        return $this->permissions->pluck('code')->values()->all();
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
