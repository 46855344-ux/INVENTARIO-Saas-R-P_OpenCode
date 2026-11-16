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
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Models\UnitConversion;
use App\Modules\Inventory\Policies\LocationPolicy;
use App\Modules\Inventory\Policies\StockBalancePolicy;
use App\Modules\Inventory\Policies\StockMovementPolicy;
use App\Modules\Inventory\Policies\UnitConversionPolicy;
use App\Modules\Inventory\Policies\UnitPolicy;
use App\Modules\Metrics\Models\AlertRule;
use App\Modules\Metrics\Models\StockAlert;
use App\Modules\Metrics\Policies\AlertRulePolicy;
use App\Modules\Metrics\Policies\StockAlertPolicy;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Models\Supplier;
use App\Modules\Purchasing\Models\Transfer;
use App\Modules\Purchasing\Policies\PurchasePolicy;
use App\Modules\Purchasing\Policies\SupplierPolicy;
use App\Modules\Purchasing\Policies\TransferPolicy;
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
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(UnitConversion::class, UnitConversionPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(StockBalance::class, StockBalancePolicy::class);
        Gate::policy(StockMovement::class, StockMovementPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(Transfer::class, TransferPolicy::class);
        Gate::policy(AlertRule::class, AlertRulePolicy::class);
        Gate::policy(StockAlert::class, StockAlertPolicy::class);
    }
}
