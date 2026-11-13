<?php

namespace App\Providers;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Policies\BrandPolicy;
use App\Modules\Catalog\Policies\CategoryPolicy;
use App\Modules\Catalog\Policies\ProductPolicy;
use App\Modules\Catalog\Policies\ProductVariantPolicy;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Policies\PermissionPolicy;
use App\Modules\Identity\Policies\RolePolicy;
use App\Modules\Identity\Policies\UserPolicy;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Los códigos `modulo.accion` se resuelven contra los permisos del rol
        // activo en la empresa de la petición (ver ADR-0003).
        Gate::before(function ($user, $ability): ?bool {
            if (! is_string($ability) || ! str_contains($ability, '.')) {
                return null;
            }

            $context = request()->attributes->get(TenantContext::class);

            if (! $context instanceof TenantContext) {
                return null;
            }

            return $context->hasPermission($ability);
        });

        Gate::policy(CompanyUser::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductVariant::class, ProductVariantPolicy::class);
    }
}
