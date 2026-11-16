<?php

namespace Database\Factories;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'action' => 'stock_count.created',
            'entity_type' => 'stock_count',
            'entity_id' => 1,
            'context' => ['status' => 'open'],
            'ip' => '127.0.0.1',
        ];
    }
}
