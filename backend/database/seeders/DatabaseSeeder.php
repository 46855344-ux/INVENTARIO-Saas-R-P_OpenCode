<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Datos de demostración: dos empresas, dos usuarios y los roles base.
     */
    public function run(): void
    {
        $this->call(IdentitySeeder::class);

        $owner = Role::query()->whereNull('company_id')->where('code', Role::CODE_OWNER)->firstOrFail();
        $adminRole = Role::query()->whereNull('company_id')->where('code', Role::CODE_ADMIN)->firstOrFail();
        $operatorRole = Role::query()->whereNull('company_id')->where('code', Role::CODE_OPERATOR)->firstOrFail();

        $main = Company::factory()->create([
            'name' => 'Empresa Demo',
            'slug' => 'empresa-demo',
        ]);

        $branch = Company::factory()->create([
            'name' => 'Empresa Sucursal',
            'slug' => 'empresa-sucursal',
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrador Demo',
            'email' => 'admin@empresa-demo.test',
        ]);

        $admin->companies()->attach($main->id, [
            'status' => Company::STATUS_ACTIVE,
            'role_id' => $owner->id,
        ]);
        $admin->companies()->attach($branch->id, [
            'status' => Company::STATUS_ACTIVE,
            'role_id' => $adminRole->id,
        ]);

        $operator = User::factory()->create([
            'name' => 'Operador Demo',
            'email' => 'operador@empresa-demo.test',
        ]);

        $operator->companies()->attach($main->id, [
            'status' => Company::STATUS_ACTIVE,
            'role_id' => $operatorRole->id,
        ]);
    }
}
