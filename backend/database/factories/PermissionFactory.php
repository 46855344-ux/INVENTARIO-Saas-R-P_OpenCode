<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'module.'.Str::lower(Str::random(8));

        return [
            'code' => $code,
            'name' => $code,
            'description' => null,
        ];
    }
}
