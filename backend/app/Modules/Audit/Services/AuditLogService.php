<?php

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    /**
     * @return array<string, mixed>
     */
    public function record(
        TenantContext $context,
        ?User $user,
        string $action,
        string $entityType,
        ?int $entityId,
        array $payload = [],
    ): array {
        return AuditLog::query()->create([
            'company_id' => $context->company->id,
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context' => $payload === [] ? null : $payload,
            'ip' => app()->runningInConsole() ? null : request()->ip(),
            'created_at' => now(),
        ])->toArray();
    }

    /**
     * @param  array{action?: string|null, entity_type?: string|null, from?: string|null, to?: string|null, per_page?: int|null}  $filters
     */
    public function paginate(TenantContext $context, array $filters): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('user')
            ->where('company_id', $context->company->id)
            ->when(isset($filters['action']), fn ($query) => $query->where('action', $filters['action']))
            ->when(isset($filters['entity_type']), fn ($query) => $query->where('entity_type', $filters['entity_type']))
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }
}
