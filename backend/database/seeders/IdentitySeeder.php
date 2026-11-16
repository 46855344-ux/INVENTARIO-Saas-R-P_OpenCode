<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Catálogo base de permisos y roles globales (ADR-0003).
 */
class IdentitySeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const PERMISSIONS = [
        'identity.users.view' => 'Ver usuarios',
        'identity.users.manage' => 'Administrar usuarios',
        'identity.roles.view' => 'Ver roles',
        'identity.roles.manage' => 'Administrar roles',
        'catalog.view' => 'Ver catálogo',
        'catalog.manage' => 'Administrar catálogo',
        'settings.view' => 'Ver configuración de inventario',
        'settings.manage' => 'Administrar configuración de inventario',
        'inventory.view' => 'Ver inventario',
        'inventory.move' => 'Registrar movimientos',
        'inventory.adjust' => 'Ajustar inventario',
        'inventory.approve' => 'Aprobar inventario',
        'purchases.view' => 'Ver proveedores y compras',
        'purchases.manage' => 'Administrar proveedores y compras',
        'purchases.receive' => 'Confirmar recepción de compras',
        'transfers.view' => 'Ver traslados',
        'transfers.manage' => 'Registrar traslados',
        'metrics.view' => 'Ver métricas de inventario',
        'alerts.view' => 'Ver alertas de inventario',
        'alerts.manage' => 'Administrar reglas y alertas',
        'counts.view' => 'Ver conteos físicos',
        'counts.manage' => 'Registrar y revisar conteos',
        'counts.approve' => 'Aprobar diferencias de conteo',
        'assistant.use' => 'Consultar el asistente de inventario',
        'metrics.costs.view' => 'Ver costos y márgenes',
        'audit.view' => 'Ver auditoría',
    ];

    /**
     * @var array<string, array{name: string, description: string, permissions: list<string>|string}>
     */
    private const ROLES = [
        'owner' => [
            'name' => 'Propietario',
            'description' => 'Acceso total a la empresa.',
            'permissions' => '*',
        ],
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Gestión operativa y de usuarios.',
            'permissions' => [
                'identity.users.view',
                'identity.users.manage',
                'identity.roles.view',
                'catalog.view',
                'catalog.manage',
                'settings.view',
                'settings.manage',
                'inventory.view',
                'inventory.move',
                'inventory.adjust',
                'inventory.approve',
                'purchases.view',
                'purchases.manage',
                'purchases.receive',
                'transfers.view',
                'transfers.manage',
                'metrics.view',
                'alerts.view',
                'alerts.manage',
                'counts.view',
                'counts.manage',
                'counts.approve',
                'assistant.use',
                'metrics.costs.view',
                'audit.view',
            ],
        ],
        'operator' => [
            'name' => 'Operador',
            'description' => 'Registro de movimientos de inventario.',
            'permissions' => [
                'catalog.view',
                'settings.view',
                'inventory.view',
                'inventory.move',
                'purchases.view',
                'transfers.view',
                'transfers.manage',
                'metrics.view',
                'alerts.view',
                'counts.view',
                'counts.manage',
                'assistant.use',
            ],
        ],
        'viewer' => [
            'name' => 'Consulta',
            'description' => 'Lectura de inventario.',
            'permissions' => [
                'catalog.view',
                'settings.view',
                'inventory.view',
                'purchases.view',
                'transfers.view',
                'metrics.view',
                'alerts.view',
                'counts.view',
                'assistant.use',
            ],
        ],
    ];

    public function run(): void
    {
        $permissions = collect(self::PERMISSIONS)->map(fn (string $name, string $code): Permission => Permission::query()->firstOrCreate(
            ['code' => $code],
            ['name' => $name],
        ));

        foreach (self::ROLES as $code => $definition) {
            $role = Role::query()->firstOrCreate(
                ['company_id' => null, 'code' => $code],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                ],
            );

            $rolePermissions = $definition['permissions'] === '*'
                ? $permissions->pluck('id')->all()
                : $permissions
                    ->filter(fn (Permission $permission): bool => in_array($permission->code, $definition['permissions'], true))
                    ->pluck('id')
                    ->all();

            $role->permissions()->sync($rolePermissions);
        }
    }
}
