<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => null,
            'code' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'name' => Str::title($name),
            'description' => null,
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    /**
     * Crea (si hace falta) los permisos indicados y los asocia al rol.
     *
     * @param  list<string>  $codes
     */
    public function withPermissions(array $codes): static
    {
        return $this->afterCreating(function (Role $role) use ($codes) {
            $permissionIds = collect($codes)->map(function (string $code): int {
                return Permission::query()->firstOrCreate(
                    ['code' => $code],
                    ['name' => $code],
                )->id;
            });

            $role->permissions()->syncWithoutDetaching($permissionIds->all());
        });
    }
}
