<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Datos de demostración de US-0101: dos empresas y dos usuarios.
     * El administrador pertenece a ambas empresas; el operador, solo a la principal.
     */
    public function run(): void
    {
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

        $admin->companies()->attach([$main->id, $branch->id], ['status' => 'active']);

        $operator = User::factory()->create([
            'name' => 'Operador Demo',
            'email' => 'operador@empresa-demo.test',
        ]);

        $operator->companies()->attach($main->id, ['status' => 'active']);
    }
}
